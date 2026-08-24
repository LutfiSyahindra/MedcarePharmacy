<script>
    $(document).ready(function() {
        const userSubmitDefault = '<i class="mdi mdi-content-save-outline"></i>Simpan User';
        const userSubmitUpdate = '<i class="mdi mdi-content-save-edit-outline"></i>Update User';
        const assignRolesDefault = '<i class="mdi mdi-account-check-outline"></i>Simpan Role';

        AuthUI.wirePasswordToggles();

        $('#usersModal').on('show.bs.modal', function() {
            const form = $('#signupForm');
            $('#usersModalLabel').text('Tambah User');
            form.trigger('reset');
            AuthUI.clearValidation('#signupForm');
            $('#userId').val('');
            $('#submitForm').html(userSubmitDefault).prop('disabled', false);
        });

        let userTable = $('#tableUsers').DataTable(AuthUI.dataTableOptions({
            ajax: {
                url: "{{ route("users.table") }}",
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
                        const initials = AuthUI.getInitials(data);
                        return `
                            <div class="auth-identity">
                                <span class="auth-avatar">${initials}</span>
                                <div>
                                    <strong title="${safeName}">${safeName}</strong>
                                    <small>ID #${AuthUI.escapeHtml(row.id)}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data, type) {
                        if (type !== 'display') return data;

                        const safeEmail = AuthUI.escapeHtml(data);
                        return `
                            <a href="mailto:${safeEmail}" class="auth-email-link">
                                <i class="mdi mdi-email-outline"></i>
                                ${safeEmail}
                            </a>
                        `;
                    }
                },
                {
                    data: 'stock_opname_transactions_count',
                    name: 'stock_opname_transactions_count',
                    render: function(data, type, row) {
                        const count = Number(data) || 0;

                        if (type !== 'display') return count;

                        return `
                            <button type="button" class="user-opname-count ${count ? 'has-transactions' : ''}"
                                onclick="showUserStockOpnames(${row.id})"
                                title="Lihat transaksi Stock Opname ${AuthUI.escapeHtml(row.name)}">
                                <i class="mdi mdi-clipboard-text-clock-outline"></i>
                                <strong>${count}</strong>
                                <span>transaksi</span>
                            </button>
                        `;
                    }
                },
                {
                    data: 'status',
                    name: 'status',
                    render: function(data, type, row) {
                        const isActive = Number(data) === 1;
                        const checked = isActive ? 'checked' : '';
                        const label = isActive ? 'Aktif' : 'Nonaktif';
                        const statusClass = isActive ? 'is-active' : 'is-inactive';

                        return `
                            <div class="auth-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-status"
                                        data-id="${row.id}" ${checked} id="switch${row.id}">
                                    <label class="form-check-label" for="switch${row.id}"></label>
                                </div>
                                <span class="auth-status-text ${statusClass}">${label}</span>
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
        }));

        AuthUI.initTableTools({
            table: userTable,
            tableSelector: '#tableUsers',
            searchSelector: '#usersSearch',
            totalTarget: '#usersTotalCount',
            filteredTarget: '#usersFilteredCount',
            selectedTarget: '#usersSelectedCount'
        });

        $('#tableUsers').on('change', '.toggle-status', function() {
            const $toggle = $(this);
            const userId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;
            const $statusText = $toggle.closest('.auth-status-wrap').find('.auth-status-text');

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("users.updateStatus") }}",
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: userId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status !== 1)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status user berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus !== 1)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: 'Terjadi kesalahan saat mengubah status.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $('#signupForm').on('submit', function(e) {
            e.preventDefault();
            AuthUI.clearValidation('#signupForm');

            const formData = $(this).serialize();
            const userId = $('#userId').val();
            const url = userId ? "{{ route("users.update", ":id") }}".replace(':id', userId) :
                "{{ route("users.store") }}";
            const method = userId ? 'PUT' : 'POST';
            const normalLabel = userId ? userSubmitUpdate : userSubmitDefault;

            Swal.fire({
                title: userId ? 'Perbarui data user ini?' : 'Tambahkan user baru?',
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
                            $('#usersModal').modal('hide');

                            Swal.fire({
                                icon: 'success',
                                title: response.message,
                                toast: true,
                                position: 'top-end',
                                timer: 3000,
                                timerProgressBar: true,
                                showConfirmButton: false,
                            });

                            $('#signupForm')[0].reset();
                            $('#userId').val('');
                            userTable.ajax.reload(null, false);
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
                            text: 'Terjadi kesalahan saat menyimpan user.'
                        });
                    },
                    complete: function() {
                        AuthUI.setButtonLoading('#submitForm', false, 'Menyimpan...', normalLabel);
                    }
                });
            });
        });

        window.editUsers = function(userId) {
            const modal = $('#usersModal');
            modal.modal('show');

            $('#signupForm')[0].reset();
            AuthUI.clearValidation('#signupForm');
            $('#userId').val(userId);
            $('#submitForm').html(userSubmitUpdate).prop('disabled', false);

            $.ajax({
                url: "{{ route("users.edit", ":id") }}".replace(':id', userId),
                method: 'GET',
                success: function(response) {
                    $('#usersModalLabel').text('Edit User');
                    $('#name').val(response.name);
                    $('#email').val(response.email);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data user. Silakan coba lagi.',
                    });
                    modal.modal('hide');
                }
            });
        }

        window.deleteUsers = function(id) {
            Swal.fire({
                title: 'Hapus user ini?',
                text: 'Data user akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("users.delete", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Dihapus',
                                text: response.message,
                                timer: 1800,
                                showConfirmButton: false
                            });
                            userTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus user.', 'error');
                    }
                });
            });
        }

        function formatOpnameDate(value, includeTime = false) {
            if (!value) return '-';

            const parsed = new Date(String(value).replace(' ', 'T'));
            if (Number.isNaN(parsed.getTime())) return AuthUI.escapeHtml(value);

            const options = includeTime
                ? { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' }
                : { day: '2-digit', month: 'short', year: 'numeric' };

            return new Intl.DateTimeFormat('id-ID', options).format(parsed);
        }

        function opnameStatusClass(status) {
            return {
                counting: 'is-counting',
                awaiting_verification: 'is-waiting',
                awaiting_approval: 'is-waiting',
                approved: 'is-approved',
                adjusted: 'is-completed'
            }[status] || 'is-draft';
        }

        function renderUserStockOpnames(response) {
            const transactions = response.transactions || [];
            const summary = response.summary || {};
            const safeName = AuthUI.escapeHtml(response.user?.name || 'User');
            const safeEmail = AuthUI.escapeHtml(response.user?.email || '');

            $('#userStockOpnamesModalLabel').text(`Transaksi Stock Opname - ${response.user?.name || 'User'}`);
            $('#userStockOpnamesSubtitle').html(
                `Riwayat keterlibatan <strong>${safeName}</strong>${safeEmail ? ` (${safeEmail})` : ''}.`
            );

            ['total', 'counting', 'waiting', 'completed'].forEach(function(key) {
                $(`#userStockOpnamesSummary [data-summary="${key}"]`).text(Number(summary[key]) || 0);
            });

            $('#userStockOpnamesSummary').prop('hidden', false);

            if (!transactions.length) {
                $('#userStockOpnamesEmpty').prop('hidden', false);
                $('#userStockOpnamesTableWrap').prop('hidden', true);
                return;
            }

            const rows = transactions.map(function(transaction) {
                const roleBadges = (transaction.roles || []).map(function(role) {
                    return `<span class="user-opname-role">${AuthUI.escapeHtml(role)}</span>`;
                }).join('');
                const countedItems = Number(transaction.counted_items) > 0
                    ? `<small><i class="mdi mdi-format-list-checks"></i> ${Number(transaction.counted_items)} item dihitung</small>`
                    : '';
                const lastActivity = (transaction.activities || [])[0];
                const activityHtml = lastActivity
                    ? `<strong>${AuthUI.escapeHtml(lastActivity.label)}</strong>
                       <small>${formatOpnameDate(lastActivity.performed_at, true)}</small>
                       ${lastActivity.note ? `<span title="${AuthUI.escapeHtml(lastActivity.note)}">${AuthUI.escapeHtml(lastActivity.note)}</span>` : ''}`
                    : '<span class="auth-muted">Aktivitas tidak tercatat</span>';

                return `
                    <tr>
                        <td>
                            <div class="user-opname-primary">
                                <strong>${AuthUI.escapeHtml(transaction.nomor)}</strong>
                                <small><i class="mdi mdi-calendar-blank-outline"></i> ${formatOpnameDate(transaction.tanggal_opname)}</small>
                            </div>
                        </td>
                        <td>
                            <div class="user-opname-primary">
                                <strong>${AuthUI.escapeHtml(transaction.branch)}</strong>
                                <small><i class="mdi mdi-map-marker-outline"></i> ${AuthUI.escapeHtml(transaction.lokasi)}</small>
                            </div>
                        </td>
                        <td><span class="user-opname-status ${opnameStatusClass(transaction.status)}">${AuthUI.escapeHtml(transaction.status_label)}</span></td>
                        <td><div class="user-opname-roles">${roleBadges || '<span class="auth-muted">Pelaksana</span>'}${countedItems}</div></td>
                        <td><div class="user-opname-activity">${activityHtml}</div></td>
                    </tr>
                `;
            }).join('');

            $('#userStockOpnamesRows').html(rows);
            $('#userStockOpnamesEmpty').prop('hidden', true);
            $('#userStockOpnamesTableWrap').prop('hidden', false);
        }

        window.showUserStockOpnames = function(userId) {
            const modal = $('#userStockOpnamesModal');

            $('#userStockOpnamesModalLabel').text('Transaksi Stock Opname');
            $('#userStockOpnamesSubtitle').text('Riwayat keterlibatan user dalam Stock Opname.');
            $('#userStockOpnamesRows').empty();
            $('#userStockOpnamesSummary, #userStockOpnamesEmpty, #userStockOpnamesTableWrap').prop('hidden', true);
            $('#userStockOpnamesLoading').prop('hidden', false);
            modal.modal('show');

            $.ajax({
                url: "{{ route('users.stockOpnames', ':id') }}".replace(':id', userId),
                type: 'GET',
                success: renderUserStockOpnames,
                error: function(xhr) {
                    modal.modal('hide');
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: xhr.responseJSON?.message || 'Transaksi Stock Opname user gagal dimuat.'
                    });
                },
                complete: function() {
                    $('#userStockOpnamesLoading').prop('hidden', true);
                }
            });
        }

        function loadRolesOptions(selectedIds = []) {
            const normalizedSelected = selectedIds.map(String);

            return $.ajax({
                url: '{{ route("users.dataRoles") }}',
                type: 'GET'
            }).done(function(response) {
                const rolesSelect = $('#rolesSelect');

                if (rolesSelect.hasClass('select2-hidden-accessible')) {
                    rolesSelect.select2('destroy');
                }

                rolesSelect.empty();
                response.forEach(function(roles) {
                    const isSelected = normalizedSelected.includes(String(roles.id)) ? 'selected' : '';
                    rolesSelect.append(
                        `<option value="${roles.id}" ${isSelected}>${AuthUI.escapeHtml(roles.name)}</option>`
                    );
                });

                rolesSelect.select2({
                    dropdownParent: $('#assignRolesModal'),
                    placeholder: "Pilih roles",
                    allowClear: true,
                    width: '100%'
                });

                rolesSelect.off('change.authRoles').on('change.authRoles', function() {
                    AuthUI.updateSelectCount('#rolesSelect', '#rolesSelectionCount');
                });

                AuthUI.updateSelectCount('#rolesSelect', '#rolesSelectionCount');
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data roles.'
                });
            });
        }

        window.assignRoles = function(UsersId) {
            const modal = $('#assignRolesModal');
            modal.modal('show');
            $('#assignRolesModalLabel').text('Assign Roles');
            $('#userssId').val(UsersId);
            $('#assignRoles').html(assignRolesDefault).prop('disabled', false);
            $('#rolesSelectionCount').text('0');

            const optionsRequest = loadRolesOptions([]);

            $.ajax({
                url: "{{ route("users.getUserRoles", ":id") }}".replace(':id', UsersId),
                type: "GET",
                success: function(res) {
                    if (res.status) {
                        optionsRequest.done(function() {
                            $('#rolesSelect').val((res.data || []).map(String)).trigger('change');
                        });
                    }
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data roles.'
                    });
                }
            });
        }

        $('#selectAllRoles').on('click', function() {
            $('#rolesSelect option').prop('selected', true);
            $('#rolesSelect').trigger('change');
        });

        $('#clearRolesSelection').on('click', function() {
            $('#rolesSelect').val(null).trigger('change');
        });

        $('#assignRolesForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            AuthUI.setButtonLoading('#assignRoles', true, 'Menyimpan...', assignRolesDefault);

            $.ajax({
                url: "{{ route("users.assignRoles") }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.status) {
                        $('#assignRolesModal').modal('hide');
                        $('#assignRolesForm')[0].reset();
                        $('#rolesSelect').val(null).trigger('change');
                        userTable.ajax.reload(null, false);

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
                        text: xhr.status === 422 ? 'Pilih minimal satu role.' : 'Terjadi kesalahan server.'
                    });
                },
                complete: function() {
                    AuthUI.setButtonLoading('#assignRoles', false, 'Menyimpan...', assignRolesDefault);
                }
            });
        });
    });
</script>
