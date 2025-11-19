<script>
    $(document).ready(function() {

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        // --- Fungsi untuk memuat opsi kategori utama ke select2
        function loadKategoriUtamaOptions(targetSelect, selectedIds = []) {
            // pastikan selectedIds selalu array
            if (!Array.isArray(selectedIds)) {
                selectedIds = [selectedIds?.toString()];
            }

            return $.ajax({
                url: '{{ route("kategoriMain.kategoriUtama") }}',
                type: 'GET',
                success: function(response) {
                    console.log(response);
                    targetSelect.empty();

                    // Tambahkan opsi satu per satu
                    response.forEach(function(kategoriUtama) {
                        const isSelected = selectedIds.includes(kategoriUtama.id
                                .toString()) ?
                            'selected' :
                            '';
                        targetSelect.append(
                            `<option value="${kategoriUtama.id}" ${isSelected}>${kategoriUtama.name}</option>`
                        );
                    });

                    // Inisialisasi select2
                    targetSelect.select2({
                        dropdownParent: $('#mainCategoryModal'),
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
        $('#mainCategoryModal').on('show.bs.modal', function() {
            let form = $('#mainCategoryForm');
            $('#mainCategoryModalLabel').text('ADD KATEGORI OBAT');
            form.trigger('reset');
            $('#submitForm').text('Add');
            $('#addInput').show();

            // reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#mainCategoryId').val('');

            // reset input-wrapper jadi hanya 1 row
            $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                                <label class="form-label">Kategori Utama</label>
                                <select name="category_id[]" id="kategoriUtamaSelect"
                                    class="js-kategori-utama form-select" data-width="100%"></select>
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
            loadKategoriUtamaOptions($('.js-kategori-utama'));
        });

        // --- Tambah input baru
        $(document).on('click', '#addInput', function() {
            let newInput = `
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                                <label class="form-label">Kategori Utama</label>
                                <select name="category_id[]" id="kategoriUtamaSelect"
                                    class="js-kategori-utama form-select" data-width="100%"></select>
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
            `;
            $('#input-wrapper').append(newInput);
            // Load kategori utama untuk elemen pertama
            loadKategoriUtamaOptions($('.js-kategori-utama'));
        });

        // --- Hapus input tertentu
        $(document).on('click', '.remove-input', function() {
            $(this).closest('.input-group-item').remove();
        });

        // --- DataTable
        let mainCategoryTable = $('#tableMainKategori').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
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
                    name: 'category_id'
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
        $('#searchKategoriUtama').on('keyup', function() {
            mainCategoryTable.search(this.value).draw();
        });

        // --- Submit form
        $('#mainCategoryForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let mainCategoryId = $('#mainCategoryId').val();

            let url = mainCategoryId ?
                "{{ route("kategori.mainKategori.update", ":id") }}".replace(':id', mainCategoryId) :
                "{{ route("kategori.mainKategori.store") }}";

            let method = mainCategoryId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#mainCategoryModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#mainCategoryForm')[0].reset();
                        $('#mainCategoryId').val('');
                        mainCategoryTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#mainCategoryForm').find('.invalid-feedback').text('');
                        $('#mainCategoryForm').find('.form-control').removeClass(
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
        window.editMainCategory = function(id) {
            $.ajax({
                url: "{{ route("kategori.mainKategori.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    // isi modal
                    $('#mainCategoryModal').modal('show');
                    $('#mainCategoryModalLabel').text('EDIT KATEGORI OBAT');
                    $('#submitForm').text('Update');

                    // sembunyikan tombol tambah input (supaya tidak bisa multiple)
                    $('#addInput').hide();

                    // set hidden ID
                    $('#mainCategoryId').val(response.id);

                    // render hanya 1 row input
                    $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Kategori Utama</label>
                        <select name="category_id" class="js-kategori-utama form-select" data-width="100%"></select>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input class="form-control" name="code" type="text" value="${response.code}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Kategori Obat</label>
                        <input class="form-control" name="name" type="text" value="${response.name}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            `);
                    // ambil elemen dropdown yang baru dibuat
                    const $select = $('.js-kategori-utama');

                    // panggil fungsi load dan berikan ID yang sedang aktif
                    loadKategoriUtamaOptions($select, response.category_id);
                }
            });
        }

        // --- Hapus
        window.deleteMainCategory = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Main Category ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("kategori.mainKategori.destroy", ":id") }}".replace(
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
                                mainCategoryTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Main Category.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // --- Download Template
        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("kategori.mainKategori.exportTemplate") }}";
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
                            title: 'Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati (sudah ada).</p>
                            `,
                            timer: 2500,
                            showConfirmButton: false,
                            willClose: () => {
                                // Tutup modal
                                $('#mainCategoryModalExcell').modal('hide');

                                // Reload DataTable jika sudah diinisialisasi
                                if (typeof mainCategoryTable !== 'undefined') {
                                    mainCategoryTable.ajax.reload(null,
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
