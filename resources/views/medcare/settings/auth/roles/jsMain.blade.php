<script>
    $(document).ready(function() {
        const roleSubmitDefault = '<i class="mdi mdi-content-save-outline"></i>Simpan Role';
        const roleSubmitUpdate = '<i class="mdi mdi-content-save-edit-outline"></i>Update Role';
        const assignPermissionsDefault = '<i class="mdi mdi-shield-check-outline"></i>Simpan Permission';

        $('#rolesModal').on('show.bs.modal', function() {
            const form = $('#rolesForm');
            $('#rolesModalLabel').text('Tambah Role');
            form.trigger('reset');
            AuthUI.clearValidation('#rolesForm');
            $('#rolesId').val('');
            $('#submitForm').html(roleSubmitDefault).prop('disabled', false);
        });

        let rolesTable = $('#tableRoles').DataTable(AuthUI.dataTableOptions({
            ajax: {
                url: "{{ route("roles.table") }}",
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
                                    <small>Role ID #${AuthUI.escapeHtml(row.id)}</small>
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
            table: rolesTable,
            tableSelector: '#tableRoles',
            searchSelector: '#rolesSearch',
            totalTarget: '#rolesTotalCount',
            filteredTarget: '#rolesFilteredCount',
            selectedTarget: '#rolesSelectedCount'
        });

        $('#rolesForm').on('submit', function(e) {
            e.preventDefault();
            AuthUI.clearValidation('#rolesForm');

            const formData = $(this).serialize();
            const rolesId = $('#rolesId').val();
            const url = rolesId ? "{{ route("roles.update", ":id") }}".replace(':id', rolesId) :
                "{{ route("roles.store") }}";
            const method = rolesId ? 'PUT' : 'POST';
            const normalLabel = rolesId ? roleSubmitUpdate : roleSubmitDefault;

            Swal.fire({
                title: rolesId ? 'Perbarui role ini?' : 'Tambahkan role baru?',
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
                            $('#rolesModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });

                            $('#rolesForm')[0].reset();
                            $('#rolesId').val('');
                            rolesTable.ajax.reload(null, false);
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
                            text: 'Terjadi kesalahan saat menyimpan role.'
                        });
                    },
                    complete: function() {
                        AuthUI.setButtonLoading('#submitForm', false, 'Menyimpan...', normalLabel);
                    }
                });
            });
        });

        window.editRoles = function(RolesId) {
            const modal = $('#rolesModal');
            modal.modal('show');

            $('#rolesForm')[0].reset();
            AuthUI.clearValidation('#rolesForm');
            $('#rolesId').val(RolesId);
            $('#submitForm').html(roleSubmitUpdate).prop('disabled', false);

            $.ajax({
                url: "{{ route("roles.edit", ":id") }}".replace(':id', RolesId),
                method: 'GET',
                success: function(response) {
                    $('#rolesModalLabel').text('Edit Role');
                    $('#name').val(response.name);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data role. Silakan coba lagi.',
                    });
                    modal.modal('hide');
                }
            });
        }

        window.deleteRoles = function(RolesId) {
            Swal.fire({
                title: 'Hapus role ini?',
                text: 'Role yang dihapus tidak bisa digunakan lagi untuk assignment.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("roles.delete", ":id") }}".replace(':id', RolesId),
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
                            rolesTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Gagal menghapus data role. Silakan coba lagi.',
                        });
                    }
                });
            });
        }

        function loadPermissionsOptions(selectedIds = []) {
            const normalizedSelected = selectedIds.map(String);

            return $.ajax({
                url: '{{ route("roles.dataPermissions") }}',
                type: 'GET'
            }).done(function(response) {
                const permissionsSelect = $('#permissionsSelect');

                if (permissionsSelect.hasClass('select2-hidden-accessible')) {
                    permissionsSelect.select2('destroy');
                }

                permissionsSelect.empty();
                response.forEach(function(permissions) {
                    const isSelected = normalizedSelected.includes(String(permissions.id)) ? 'selected' : '';
                    permissionsSelect.append(
                        `<option value="${permissions.id}" ${isSelected}>${AuthUI.escapeHtml(permissions.name)}</option>`
                    );
                });

                permissionsSelect.select2({
                    dropdownParent: $('#assignPermissionsModal'),
                    placeholder: "Pilih permissions",
                    allowClear: true,
                    width: '100%'
                });

                permissionsSelect.off('change.authPermissions').on('change.authPermissions', function() {
                    AuthUI.updateSelectCount('#permissionsSelect', '#permissionsSelectionCount');
                });

                AuthUI.updateSelectCount('#permissionsSelect', '#permissionsSelectionCount');
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data permissions.'
                });
            });
        }

        window.assignPermissions = function(RolesId) {
            const modal = $('#assignPermissionsModal');
            modal.modal('show');
            $('#assignPermissionsModalLabel').text('Assign Permissions');
            $('#rolessId').val(RolesId);
            $('#assignPermissions').html(assignPermissionsDefault).prop('disabled', false);
            $('#permissionsSelectionCount').text('0');

            const optionsRequest = loadPermissionsOptions([]);

            $.ajax({
                url: "{{ route("roles.getRolePermissions", ":id") }}".replace(':id', RolesId),
                type: "GET",
                success: function(res) {
                    if (res.status) {
                        optionsRequest.done(function() {
                            $('#permissionsSelect').val((res.data || []).map(String)).trigger('change');
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data permissions.'
                    });
                }
            });
        }

        $('#selectAllPermissions').on('click', function() {
            $('#permissionsSelect option').prop('selected', true);
            $('#permissionsSelect').trigger('change');
        });

        $('#clearPermissionsSelection').on('click', function() {
            $('#permissionsSelect').val(null).trigger('change');
        });

        $('#assignPermissionsForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            AuthUI.setButtonLoading('#assignPermissions', true, 'Menyimpan...', assignPermissionsDefault);

            $.ajax({
                url: "{{ route("roles.assignPermissions") }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.status) {
                        $('#assignPermissionsModal').modal('hide');
                        $('#assignPermissionsForm')[0].reset();
                        $('#permissionsSelect').val(null).trigger('change');
                        rolesTable.ajax.reload(null, false);

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                        return;
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: xhr.status === 422 ? 'Pilih minimal satu permission.' : 'Terjadi kesalahan server.'
                    });
                },
                complete: function() {
                    AuthUI.setButtonLoading('#assignPermissions', false, 'Menyimpan...', assignPermissionsDefault);
                }
            });
        });
    });
</script>
