<script>
    $(document).ready(function() {

        // --- Inisialisasi Select2
        $('#reference_idSelect').select2({
            dropdownParent: $('#marginsModal'),
            placeholder: '-- Pilih Reference --'
        });

        // --- Reset modal ketika dibuka
        $('#marginsModal').on('show.bs.modal', function() {
            let form = $('#marginsForm');
            $('#marginsModalLabel').text('ADD MARGINS');
            form.trigger('reset');
            $('#submitForm').text('Add');

            // Reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control, .form-select').removeClass('is-invalid');
            $('#marginsId').val('');

            // --- Reset dropdown Reference
            let referenceSelect = $('#reference_idSelect');
            referenceSelect.empty()
                .append('<option value="">-- Pilih Reference --</option>')
                .trigger('change');

            // --- Kembalikan ke mode multiple (karena ini mode tambah)
            referenceSelect.attr('multiple', 'multiple');
            referenceSelect.attr('name', 'reference_id[]'); // pastikan array kembali

            // --- Reset dropdown Tingkat
            $('#tingkat').val('').trigger('change');
        });

        // --- DataTable
        let marginTable = $('#tableMargin').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("margin.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'reference_id',
                    name: 'reference_id'
                },
                {
                    data: 'faktor_jual',
                    name: 'faktor_jual'
                },
                {
                    data: 'persentase',
                    name: 'persentase'
                },
                {
                    data: 'tingkat',
                    name: 'tingkat'
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

        // --- Mengaktifkan dan Menonaktifkan Margin
        $('#tableMargin').on('change', '.toggle-status', function() {
            let marginsId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            console.log('margins ID: ' + marginsId + ', Status: ' + status);

            $.ajax({
                url: "{{ route("margin.updateStatus") }}", // pastikan route ini ada
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: marginsId
                },
                success: function(response) {
                    console.log('Status updated!');
                    // SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status Margin berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengubah status.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        // --- Pemilihan Reference Berdasarkan Tingkat
        $('#tingkat').on('change', function() {
            let tingkat = $(this).val();
            let referenceSelect = $('#reference_idSelect');
            referenceSelect.empty().append('<option value="">-- Pilih Reference --</option>');

            if (!tingkat) return; // jika kosong, hentikan

            $.ajax({
                url: "{{ route("margin.getReferences", ":tingkat") }}".replace(':tingkat',
                    tingkat),
                type: 'GET',
                success: function(response) {
                    response.forEach(item => {
                        referenceSelect.append(
                            `<option value="${item.id}">${item.nama ?? item.name}</option>`
                        );
                    });
                },
                error: function() {
                    alert('Gagal memuat data reference.');
                }
            });
        });

        // --- Submit form
        $('#marginsForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let marginsId = $('#marginsId').val();

            let url = marginsId ?
                "{{ route("margin.update", ":id") }}".replace(':id', marginsId) :
                "{{ route("margin.store") }}";

            let method = marginsId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#marginsModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#marginsForm')[0].reset();
                        $('#marginsId').val('');
                        marginTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#marginsForm').find('.invalid-feedback').text('');
                        $('#marginsForm').find('.form-control').removeClass(
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

        // --- Edit Margins
        window.editMargins = function(id) {
            $.ajax({
                url: "{{ route("margin.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    console.log(response);

                    // Tampilkan modal
                    $('#marginsModal').modal('show');
                    $('#marginsModalLabel').text('EDIT MARGINS');
                    $('#submitForm').text('Update');

                    // Set hidden ID
                    $('#marginsId').val(response.id);

                    // Ubah select jadi single
                    $('#reference_idSelect').removeAttr('multiple');
                    $('#reference_idSelect').attr('name', 'reference_id'); // bukan array

                    // Isi dropdown tingkat
                    $('#tingkat').val(response.tingkat).trigger('change');

                    // Tambahkan option secara manual ke Select2
                    if (response.reference_id) {
                        const option = new Option(
                            response.reference_text ?? `Reference ${response.reference_id}`,
                            response.reference_id,
                            true,
                            true
                        );
                        $('#reference_idSelect').append(option).trigger('change');
                    }

                    // Isi faktor jual
                    $('#faktor_jual').val(response.faktor_jual);
                },
                error: function() {
                    alert('Gagal mengambil data margin.');
                }
            });
        }

        // --- Hapus
        window.deleteMargins = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Margins ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("margin.destroy", ":id") }}".replace(
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
                                marginTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Margins.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

    });
</script>
