<script>
    $(document).ready(function() {

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        // --- Fungsi untuk memuat opsi main category ke select2
        function loadMainCategoryOptions(targetSelect, selectedIds = []) {
            // pastikan selectedIds selalu array
            if (!Array.isArray(selectedIds)) {
                selectedIds = [selectedIds?.toString()];
            }

            return $.ajax({
                url: '{{ route("kategori.subKategori.mainKategori") }}',
                type: 'GET',
                success: function(response) {
                    targetSelect.empty();

                    // Tambahkan opsi satu per satu
                    response.forEach(function(mainCategory) {
                        const isSelected = selectedIds.includes(mainCategory.id
                                .toString()) ?
                            'selected' :
                            '';
                        targetSelect.append(
                            `<option value="${mainCategory.id}" ${isSelected}>${mainCategory.name}</option>`
                        );
                    });

                    // Inisialisasi select2
                    targetSelect.select2({
                        dropdownParent: $('#subCategoryModal'),
                        placeholder: "Pilih Kategori Utama",
                        allowClear: false,
                        width: '100%'
                    });
                },
                error: function() {
                    alert('Gagal memuat data kategori!');
                }
            });
        }

        // --- Reset modal ketika dibuka
        $('#subCategoryModal').on('show.bs.modal', function() {
            let form = $('#subCategoryForm');
            $('#subCategoryModalLabel').text('ADD SUB KATEGORI OBAT');
            form.trigger('reset');
            $('#submitForm').text('Add');
            $('#addInput').show();

            // Reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#subCategoryId').val('');

            // Reset input-wrapper jadi hanya 1 row
            $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Main Kategori</label>
                        <select name="main_category_id[]" class="js-main-category form-select" data-width="100%"></select>
                    </div>
                    <div class="col-md-4">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="code[]" type="text">
                                <div class="invalid-feedback"></div>
                            </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori Obat</label>
                        <input class="form-control" name="name[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-input">Hapus</button>
                    </div>
                </div>
            `);

            // Load kategori utama untuk elemen pertama
            loadMainCategoryOptions($('.js-main-category'));
        });

        // --- Tambah input baru
        $(document).on('click', '#addInput', function() {
            let newInput = $(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Main Kategori</label>
                        <select name="main_category_id[]" class="js-main-category form-select" data-width="100%"></select>
                    </div>
                    <div class="col-md-4">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="code[]" type="text">
                                <div class="invalid-feedback"></div>
                            </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori Obat</label>
                        <input class="form-control" name="name[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-input">Hapus</button>
                    </div>
                </div>
            `);

            $('#input-wrapper').append(newInput);

            // Setelah ditambahkan ke DOM, panggil loadMainCategoryOptions untuk select baru
            loadMainCategoryOptions(newInput.find('.js-main-category'));
        });

        // --- Hapus input tertentu
        $(document).on('click', '.remove-input', function() {
            $(this).closest('.input-group-item').remove();
        });


        // --- DataTable
        let subCategoryTable = $('#tableSubKategori').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
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
                    name: 'main_category_id'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'name',
                    name: 'name'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // --- Hilangkan search default bawaan DataTables
        $('.dataTables_filter').hide();

        // --- Hubungkan search custom dengan DataTables
        $('#searchSubKategori').on('keyup', function() {
            subCategoryTable.search(this.value).draw();
        });

        // --- Submit form
        $('#subCategoryForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let subCategoryId = $('#subCategoryId').val();

            let url = subCategoryId ?
                "{{ route("kategori.subKategori.update", ":id") }}".replace(':id', subCategoryId) :
                "{{ route("kategori.subKategori.store") }}";

            let method = subCategoryId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#subCategoryModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#subCategoryForm')[0].reset();
                        $('#subCategoryId').val('');
                        subCategoryTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#subCategoryForm').find('.invalid-feedback').text('');
                        $('#subCategoryForm').find('.form-control').removeClass(
                            'is-invalid');

                        for (let key in errors) {
                            // contoh key: "code.0", "name.1"
                            let messages = errors[key];
                            errorMessages.push(messages[0]);

                            // cari input sesuai index
                            let parts = key.split('.');
                            let field = parts[0]; // code / name
                            let index = parts[1]; // index array

                            // ambil row ke-index lalu kasih error
                            let row = $('#input-wrapper .input-group-item').eq(index);
                            row.find(`input[name="${field}[]"]`).addClass('is-invalid');
                            row.find('.invalid-feedback').first().text(messages[0]);
                        }

                        // tampilkan semua error di toast juga
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
                    }
                }
            });
        });

        // --- Edit
        window.editSubCategory = function(id) {
            $.ajax({
                url: "{{ route("kategori.subKategori.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    console.log(response);
                    // tampilkan modal
                    $('#subCategoryModal').modal('show');
                    $('#subCategoryModalLabel').text('EDIT SUB KATEGORI OBAT');
                    $('#submitForm').text('Update');
                    $('#addInput').hide();

                    // set hidden ID
                    $('#subCategoryId').val(response.id);

                    // render input ke modal
                    $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Main Kategori</label>
                        <select name="main_category_id" class="js-main-category form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="code" type="text" value="${response.code}">
                                <div class="invalid-feedback"></div>
                            </div>
                    <div class="col-md-4">
                        <label class="form-label">Sub Kategori Obat</label>
                        <input class="form-control" name="name" type="text" value="${response.name}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            `);

                    // ambil elemen dropdown yang baru dibuat
                    const $select = $('.js-main-category');

                    // panggil fungsi load dan berikan ID yang sedang aktif
                    loadMainCategoryOptions($select, response.main_category_id);
                }
            });
        }

        // --- Hapus
        window.deleteSubCategory = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Sub Category ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("kategori.subKategori.destroy", ":id") }}".replace(
                            ':id',
                            id),
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire(
                                    'Dihapus!',
                                    response.message,
                                    'success'
                                );
                                subCategoryTable.ajax.reload(); // Reload DataTables
                            } else {
                                Swal.fire(
                                    'Gagal!',
                                    response.message,
                                    'error'
                                );
                            }
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Gagal!',
                                'Terjadi kesalahan saat menghapus Sub Category.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // --- Download Template
        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("kategori.subKategori.exportTemplate") }}";
        })

        // --- submitFormExcell
        $('#submitFormExcell').on('click', function() {
            let fileInput = $('#myDropify')[0];
            let file = fileInput.files[0];

            // Jika file belum dipilih
            if (!file) {
                // Tambahkan efek getar (shake)
                $('#myDropify').addClass('shake border-danger');

                // Hilangkan efek setelah 600ms
                setTimeout(() => {
                    $('#myDropify').removeClass('shake border-danger');
                }, 600);

                // Tampilkan alert
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih file Excel terlebih dahulu!',
                });

                return; // hentikan eksekusi selanjutnya
            }

            let formData = new FormData();
            formData.append('file', file);

            // Alert progress
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
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Kirim AJAX
            $.ajax({
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt
                                .total) * 100);
                            $('#uploadProgressBar')
                                .css('width', percentComplete + '%')
                                .text(percentComplete + '%');
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
                            title: 'Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati (sudah ada).</p>
                            `,
                            timer: 2500,
                            showConfirmButton: false,
                            willClose: () => {
                                // Tutup modal
                                $('#subCategoryModalExcell').modal('hide');

                                // Reload DataTable jika sudah diinisialisasi
                                if (typeof subCategoryTable !== 'undefined') {
                                    subCategoryTable.ajax.reload(null,
                                        false
                                    ); // false = tetap di halaman sekarang
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message ||
                                'Terjadi kesalahan saat import data.',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengupload file: ' + xhr.responseText,
                    });
                }
            });
        });

    });
</script>
