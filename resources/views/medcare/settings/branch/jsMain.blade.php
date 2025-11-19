<script>
    $(document).ready(function() {

        $('#branchModal').on('show.bs.modal', function() {
            let form = $('#branchForm');
            $('#branchModalLabel').text('ADD BRANCH');
            // reset form
            form.trigger('reset');
            $('#submitForm').text('Add');

            // reset error message kalau ada
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');

            // reset hidden userId
            $('#branchId').val('');
        });

        let branchTable = $('#tableBranch').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("branch.table") }}", // pastikan route ini ada
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
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row, meta) {
                        // jika status aktif = 1, checkbox dicentang
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

        $('#tableBranch').on('change', '.toggle-status', function() {
            let branchId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            console.log('branch ID: ' + branchId + ', Status: ' + status);

            $.ajax({
                url: "{{ route("branch.updateStatus") }}", // pastikan route ini ada
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: branchId
                },
                success: function(response) {
                    console.log('Status updated!');
                    // SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status branch berhasil diperbarui.',
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

        $('#branchForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let branchId = $('#branchId').val(); // Ambil ID branch jika ada
            let url = branchId ?
                "{{ route("branch.update", ":id") }}".replace(':id', branchId) :
                "{{ route("branch.store") }}"; // Pastikan store untuk branch, bukan users
            let method = branchId ? 'PUT' : 'POST'; // Tentukan metode

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#branchModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#branchForm')[0].reset();
                        $('#branchId').val(''); // Reset ID
                        branchTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        for (let key in errors) {
                            // tampilkan di bawah input
                            $(`#error-${key}`).text(errors[key][0]);
                            $(`#${key}`).addClass('is-invalid');

                            // kumpulkan untuk toast
                            errorMessages.push(errors[key][0]);
                        }

                        // tampilkan semua error di toast
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

        window.editBranch = function(id) {
            const modal = $('#branchModal');
            modal.modal('show');

            $('#branchForm')[0].reset();
            $('#branchId').val(id); // Set ID user

            $.ajax({
                url: "{{ route("branch.edit", ":id") }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    console.log(response);
                    $('#branchModalLabel').text('EDIT BRANCH'); // Ubah judul
                    $('#submitForm').text('Update'); // Ubah tombol
                    $('#code').val(response.code);
                    $('#name').val(response.name);
                    $('#address').val(response.address);
                    $('#phone').val(response.phone);
                    $('#email').val(response.email);
                },
                error: function(xhr) {
                    console.error('Gagal mengambil data user', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Failed to fetch user data. Please try again.',
                    });
                    modal.modal('hide');
                }
            });
        };

        window.deleteBranch = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Branch ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("branch.destroy", ":id") }}".replace(':id',
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
                                branchTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus branch.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

    });
</script>
