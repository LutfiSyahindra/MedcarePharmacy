<script>
    $(document).ready(function() {
        const permissionSubmitDefault = '<i class="mdi mdi-content-save-outline"></i>Simpan Permission';
        const permissionSubmitUpdate = '<i class="mdi mdi-content-save-edit-outline"></i>Update Permission';

        $('#permissionsModal').on('show.bs.modal', function() {
            const form = $('#permissionsForm');
            $('#permissionsModalLabel').text('Tambah Permission');
            form.trigger('reset');
            AuthUI.clearValidation('#permissionsForm');
            $('#permissionsId').val('');
            $('#submitForm').html(permissionSubmitDefault).prop('disabled', false);
        });

        let permissionsTable = $('#tablePermissions').DataTable(AuthUI.dataTableOptions({
            ajax: {
                url: "{{ route("permissions.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;

                        const safeName = AuthUI.escapeHtml(data);
                        return `
                            <div class="auth-identity">
                                <span class="auth-avatar">${AuthUI.getInitials(data)}</span>
                                <div>
                                    <strong title="${safeName}">${safeName}</strong>
                                    <small>Permission ID #${AuthUI.escapeHtml(row.id)}</small>
                                </div>
                            </div>
                        `;
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

        AuthUI.initTableTools({
            table: permissionsTable,
            tableSelector: '#tablePermissions',
            searchSelector: '#permissionsSearch',
            totalTarget: '#permissionsTotalCount',
            filteredTarget: '#permissionsFilteredCount',
            selectedTarget: '#permissionsSelectedCount'
        });

        $('#permissionsForm').on('submit', function(e) {
            e.preventDefault();
            AuthUI.clearValidation('#permissionsForm');

            const formData = $(this).serialize();
            const permissionsId = $('#permissionsId').val();
            const url = permissionsId ? "{{ route("permissions.update", ":id") }}".replace(':id',
                permissionsId) : "{{ route("permissions.store") }}";
            const method = permissionsId ? 'PUT' : 'POST';
            const normalLabel = permissionsId ? permissionSubmitUpdate : permissionSubmitDefault;

            Swal.fire({
                title: permissionsId ? 'Perbarui permission ini?' : 'Tambahkan permission baru?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                AuthUI.setButtonLoading('#submitForm', true, 'Menyimpan...', normalLabel);

                $.ajax({
                    url: url,
                    method: method,
                    data: formData,
                    success: function(response) {
                        if (response.status === 'success') {
                            $('#permissionsModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });

                            $('#permissionsForm')[0].reset();
                            $('#permissionsId').val('');
                            permissionsTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            for (let key in errors) {
                                $(`#error-${key}`).text(errors[key][0]);
                                $(`#${key}`).addClass('is-invalid').closest('.auth-field').addClass('has-error');
                            }
                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan permission.'
                        });
                    },
                    complete: function() {
                        AuthUI.setButtonLoading('#submitForm', false, 'Menyimpan...', normalLabel);
                    }
                });
            });
        });

        window.editPermissions = function(PermissionsId) {
            const modal = $('#permissionsModal');
            modal.modal('show');

            $('#permissionsForm')[0].reset();
            AuthUI.clearValidation('#permissionsForm');
            $('#permissionsId').val(PermissionsId);
            $('#submitForm').html(permissionSubmitUpdate).prop('disabled', false);

            $.ajax({
                url: "{{ route("permissions.edit", ":id") }}".replace(':id', PermissionsId),
                method: 'GET',
                success: function(response) {
                    $('#permissionsModalLabel').text('Edit Permission');
                    $('#name').val(response.name);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data permission. Silakan coba lagi.',
                    });
                    modal.modal('hide');
                }
            });
        }

        window.deletePermissions = function(PermissionsId) {
            Swal.fire({
                title: 'Hapus permission ini?',
                text: 'Permission yang dihapus tidak bisa dipakai lagi oleh role.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("permissions.delete", ":id") }}".replace(':id', PermissionsId),
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.status === 'success') {
                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });
                            permissionsTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghapus data permission. Silakan coba lagi.',
                        });
                    }
                });
            });
        }
    });
</script>
