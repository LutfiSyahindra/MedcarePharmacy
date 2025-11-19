<script>
    $(document).ready(function() {

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        // --- Reset modal ketika dibuka
        $('#pabrikanModal').on('show.bs.modal', function() {
            let form = $('#pabrikanForm');
            $('#pabrikanModalLabel').text('ADD PABRIKAN OBAT');
            form.trigger('reset');
            $('#submitForm').text('Add');
            $('#addInput').show();

            // reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#pabrikanId').val('');

            // reset input-wrapper jadi hanya 1 row
            $('#input-wrapper').html(`
                <div class="row g-3 mb-3 input-group-item align-items-end border-bottom pb-3">
                            <div class="col-md-2">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="kode[]" type="text" placeholder="Contoh: SNB">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pabrikan / Produksi</label>
                                <input class="form-control" name="nama[]" type="text" placeholder="Contoh: Sanbe">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Alamat</label>
                                <input class="form-control" name="alamat[]" type="text" placeholder="Alamat lengkap">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Telepon</label>
                                <input class="form-control" name="telepon[]" type="text"
                                    placeholder="Contoh: 08123456789" pattern="[0-9]*" inputmode="numeric"
                                    maxlength="15">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="email[]" type="email" placeholder="Alamat Email">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12 mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-input">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                </div>
            `);
        });

        // --- Tambah input baru
        $(document).on('click', '#addInput', function() {
            let newInput = `
                <div class="row g-3 mb-3 input-group-item align-items-end border-bottom pb-3">
                            <div class="col-md-2">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="kode[]" type="text" placeholder="Contoh: SNB">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pabrikan / Produksi</label>
                                <input class="form-control" name="nama[]" type="text" placeholder="Contoh: Sanbe">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Alamat</label>
                                <input class="form-control" name="alamat[]" type="text" placeholder="Alamat lengkap">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Telepon</label>
                                <input class="form-control" name="telepon[]" type="text"
                                    placeholder="Contoh: 08123456789" pattern="[0-9]*" inputmode="numeric"
                                    maxlength="15">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="email[]" type="email" placeholder="Alamat Email">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-12 mt-2 d-flex justify-content-end">
                                <button type="button" class="btn btn-outline-danger btn-sm remove-input">
                                    <i class="bi bi-trash"></i> Hapus
                                </button>
                            </div>
                        </div>
            `;
            $('#input-wrapper').append(newInput);
        });

        // --- Hapus input tertentu
        $(document).on('click', '.remove-input', function() {
            $(this).closest('.input-group-item').remove();
        });

        // --- DataTable
        let pabrikanTable = $('#tablePabrikan').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("pabrikan.table") }}",
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
                    name: 'kode'
                },
                {
                    data: 'nama',
                    name: 'nama'
                },
                {
                    data: 'alamat',
                    name: 'alamat'
                },
                {
                    data: 'telepon',
                    name: 'telepon'
                },
                {
                    data: 'email',
                    name: 'email'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row, meta) {
                        // jika status aktif = 1, checkbox dicentang1
                        let checked = data == 1 ? 'checked' : '';
                        return `
                        <div class="form-check form-switch mb-0">
                            <input type="checkbox" class="form-check-input toggle-status" data-id="${row.id}" ${checked} id="switch${row.id}">
                            <label class="form-check-label" for="switch${row.id}"></label>
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
        });

        // --- Hilangkan search default bawaan DataTables
        $('.dataTables_filter').hide();

        // --- Hubungkan search custom dengan DataTables
        $('#searchPabrikan').on('keyup', function() {
            pabrikanTable.search(this.value).draw();
        });

        // --- Mengaktifkan dan Menonaktifkan Margin
        $('#tablePabrikan').on('change', '.toggle-status', function() {
            let pabrikanId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            console.log('pabrikan ID: ' + pabrikanId + ', Status: ' + status);

            $.ajax({
                url: "{{ route("pabrikan.updateStatus") }}", // pastikan route ini ada
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: pabrikanId
                },
                success: function(response) {
                    console.log('Status updated!');
                    // SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status pabrikan berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengubah pabrikan.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        // --- Submit form
        $('#pabrikanForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let pabrikanId = $('#pabrikanId').val();

            let url = pabrikanId ?
                "{{ route("pabrikan.update", ":id") }}".replace(':id', pabrikanId) :
                "{{ route("pabrikan.store") }}";

            let method = pabrikanId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#pabrikanModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#pabrikanForm')[0].reset();
                        $('#pabrikanId').val('');
                        pabrikanTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#pabrikanForm').find('.invalid-feedback').text('');
                        $('#pabrikanForm').find('.form-control').removeClass(
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
        window.editPabrikan = function(id) {
            $.ajax({
                url: "{{ route("pabrikan.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    // isi modal
                    $('#pabrikanModal').modal('show');
                    $('#pabrikanModalLabel').text('EDIT PABRIKAN OBAT');
                    $('#submitForm').text('Update');

                    // sembunyikan tombol tambah input (supaya tidak bisa multiple)
                    $('#addInput').hide();

                    // set hidden ID
                    $('#pabrikanId').val(response.id);

                    // render hanya 1 row input
                    $('#input-wrapper').html(`
                            <div class="row g-3 mb-3 input-group-item align-items-end border-bottom pb-3">
                            <div class="col-md-2">
                                <label class="form-label">Kode</label>
                                <input class="form-control" name="kode" type="text" placeholder="Contoh: SNB" value="${response.kode}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Pabrikan / Produksi</label>
                                <input class="form-control" name="nama" type="text" placeholder="Contoh: Sanbe" value="${response.nama}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label">Alamat</label>
                                <input class="form-control" name="alamat" type="text" placeholder="Alamat lengkap" value="${response.alamat ?? '-'}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Telepon</label>
                                <input class="form-control" name="telepon" type="text"
                                    placeholder="Contoh: 08123456789" pattern="[0-9]*" inputmode="numeric"
                                    maxlength="15" value="${response.telepon ?? '-'}">
                                <div class="invalid-feedback"></div>
                            </div>

                            <div class="col-md-2">
                                <label class="form-label">Email</label>
                                <input class="form-control" name="email" type="email" placeholder="Alamat Email" value="${response.email ?? '-'}">
                                <div class="invalid-feedback"></div>
                            </div>
                        </div>
                    `);
                }
            });
        }

        // --- Hapus
        window.deletePabrikan = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Pabrikan ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("pabrikan.destroy", ":id") }}".replace(
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
                                pabrikanTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Pabrikan.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // --- Download Template
        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("pabrikan.exportTemplate") }}";
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
                url: "{{ route("pabrikan.import") }}",
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
                                $('#pabrikanModalExcell').modal('hide');

                                // Reload DataTable jika sudah diinisialisasi
                                if (typeof pabrikanTable !== 'undefined') {
                                    pabrikanTable.ajax.reload(null,
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
