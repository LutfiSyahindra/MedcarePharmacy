<script>
    $(document).ready(function() {

        // --- Reset modal ketika dibuka
        $('#kategoriUtamaModal').on('show.bs.modal', function() {
            let form = $('kategoriUtamaForm');
            $('#kategoriUtamaModalLabel').text('ADD KATEGORI UTAMA OBAT');
            form.trigger('reset');
            $('#submitForm').text('Add');
            $('#addInput').show();

            // reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#kategoriUtamaId').val('');

            // reset input-wrapper jadi hanya 1 row
            $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input class="form-control" name="code[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori Utama Obat</label>
                        <input class="form-control" name="name[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-input">Hapus</button>
                    </div>
                </div>
            `);
        });

        // --- Tambah input baru
        $(document).on('click', '#addInput', function() {
            let newInput = `
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input class="form-control" name="code[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori Utama Obat</label>
                        <input class="form-control" name="name[]" type="text">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="button" class="btn btn-danger btn-sm remove-input">Hapus</button>
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
        let kategoriUtamaTable = $('#tableKategoriUtama').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
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
            kategoriUtamaTable.search(this.value).draw();
        });

        // --- Submit form
        $('#kategoriUtamaForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let kategoriUtamaId = $('#kategoriUtamaId').val();

            let url = kategoriUtamaId ?
                "{{ route("kategori.kategoriUtama.update", ":id") }}".replace(':id', kategoriUtamaId) :
                "{{ route("kategori.kategoriUtama.store") }}";

            let method = kategoriUtamaId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#kategoriUtamaModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#kategoriUtamaForm')[0].reset();
                        $('#kategoriUtamaId').val('');
                        kategoriUtamaTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#kategoriUtamaForm').find('.invalid-feedback').text('');
                        $('#kategoriUtamaForm').find('.form-control').removeClass(
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
        window.editKategoriUtama = function(id) {
            $.ajax({
                url: "{{ route("kategori.kategoriUtama.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    // isi modal
                    $('#kategoriUtamaModal').modal('show');
                    $('#kategoriUtamaModalLabel').text('EDIT KATEGORI UTAMA OBAT');
                    $('#submitForm').text('Update');

                    // sembunyikan tombol tambah input (supaya tidak bisa multiple)
                    $('#addInput').hide();

                    // set hidden ID
                    $('#kategoriUtamaId').val(response.id);

                    // render hanya 1 row input
                    $('#input-wrapper').html(`
                <div class="row g-3 mb-2 input-group-item">
                    <div class="col-md-4">
                        <label class="form-label">Kode</label>
                        <input class="form-control" name="code" type="text" value="${response.code}">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Kategori Obat</label>
                        <input class="form-control" name="name" type="text" value="${response.name}">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
            `);
                }
            });
        }

        // --- Hapus
        window.deleteKategoriUtama = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Kategori Utama ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("kategori.kategoriUtama.destroy", ":id") }}".replace(
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
                                kategoriUtamaTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Kategori Utama.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

    });
</script>
