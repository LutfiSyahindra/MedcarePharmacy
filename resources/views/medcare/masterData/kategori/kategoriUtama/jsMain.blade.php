<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#kategoriUtamaModal';
        const formSelector = '#kategoriUtamaForm';
        const wrapperSelector = '#kategoriUtamaInputWrapper';
        const submitSelector = '#submitKategoriUtamaForm';
        const addButtonSelector = '#addKategoriUtamaInput';

        function kategoriUtamaRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const codeName = isEdit ? 'code' : 'code[]';
            const nameName = isEdit ? 'name' : 'name[]';
            const codeValue = CategoryUI.escapeHtml(data.code || '');
            const nameValue = CategoryUI.escapeHtml(data.name || '');

            return `
                <div class="category-batch-row">
                    <div class="category-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="${codeName}" type="text" value="${codeValue}" placeholder="Contoh: OBT">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="category-field is-name-wide">
                        <label class="form-label">Kategori Utama Obat</label>
                        <div class="category-input-shell">
                            <span class="category-input-icon"><i class="mdi mdi-tag-outline"></i></span>
                            <input class="form-control" name="${nameName}" type="text" value="${nameValue}" placeholder="Masukkan nama kategori utama">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="category-field is-action">
                            <button type="button" class="btn btn-outline-danger category-row-remove remove-kategori-utama-input" title="Hapus baris">
                                <i class="mdi mdi-delete-outline"></i>
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#kategoriUtamaModalLabel').text('Tambah Kategori Utama');
            $('#kategoriUtamaModalSubtitle').text('Buat satu atau beberapa kategori utama obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#kategoriUtamaId').val('');
            $('#kategoriUtamaBatchToolbar').show();
            $form.trigger('reset');
            CategoryUI.clearValidation(formSelector);
            $(wrapperSelector).html(kategoriUtamaRow());
            CategoryUI.updateBatchCount(wrapperSelector, '#kategoriUtamaRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            $(wrapperSelector).append(kategoriUtamaRow());
            CategoryUI.updateBatchCount(wrapperSelector, '#kategoriUtamaRowCount');
        });

        $(document).on('click', '.remove-kategori-utama-input', function() {
            if ($(wrapperSelector).find('.category-batch-row').length <= 1) {
                $(this).closest('.category-batch-row').find('input').val('');
                CategoryUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            $(this).closest('.category-batch-row').remove();
            CategoryUI.updateBatchCount(wrapperSelector, '#kategoriUtamaRowCount');
        });

        const kategoriUtamaTable = $('#tableKategoriUtama').DataTable(CategoryUI.dataTableOptions({
            ajax: {
                url: "{{ route("kategori.kategoriUtama.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
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
                        return CategoryUI.identity(data, 'Kategori utama');
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
            table: kategoriUtamaTable,
            tableSelector: '#tableKategoriUtama',
            searchSelector: '#kategoriUtamaSearch',
            totalTarget: '#kategoriUtamaTotal',
            filteredTarget: '#kategoriUtamaFiltered',
            selectedTarget: '#kategoriUtamaSelected'
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const kategoriUtamaId = $('#kategoriUtamaId').val();
            const url = kategoriUtamaId ?
                "{{ route("kategori.kategoriUtama.update", ":id") }}".replace(':id', kategoriUtamaId) :
                "{{ route("kategori.kategoriUtama.store") }}";
            const method = kategoriUtamaId ? 'PUT' : 'POST';
            const normalHtml = kategoriUtamaId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CategoryUI.clearValidation(formSelector);
            CategoryUI.setButtonLoading(submitSelector, true, kategoriUtamaId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CategoryUI.toast('success', response.message);
                        kategoriUtamaTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CategoryUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CategoryUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CategoryUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan kategori utama.');
                },
                complete: function() {
                    CategoryUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editKategoriUtama = function(id) {
            $.ajax({
                url: "{{ route("kategori.kategoriUtama.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#kategoriUtamaModalLabel').text('Edit Kategori Utama');
                    $('#kategoriUtamaModalSubtitle').text('Perbarui kode dan nama kategori utama yang dipilih.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#kategoriUtamaBatchToolbar').hide();
                    $('#kategoriUtamaId').val(response.id);
                    $(wrapperSelector).html(kategoriUtamaRow('edit', response));
                    CategoryUI.clearValidation(formSelector);
                    CategoryUI.updateBatchCount(wrapperSelector, '#kategoriUtamaRowCount');
                },
                error: function() {
                    CategoryUI.toast('error', 'Gagal Memuat', 'Data kategori utama tidak bisa dimuat.');
                }
            });
        };

        window.deleteKategoriUtama = function(id) {
            Swal.fire({
                title: 'Hapus kategori utama?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("kategori.kategoriUtama.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CategoryUI.toast('success', 'Berhasil Dihapus', response.message);
                            kategoriUtamaTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus kategori utama.', 'error');
                    }
                });
            });
        };
    });
</script>
