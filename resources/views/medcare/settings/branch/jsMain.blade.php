<script>
    $(document).ready(function() {
        const branchSubmitDefault = '<i class="mdi mdi-content-save-outline"></i>Simpan Branch';
        const branchSubmitUpdate = '<i class="mdi mdi-content-save-edit-outline"></i>Update Branch';

        $('#branchModal').on('show.bs.modal', function() {
            const form = $('#branchForm');
            $('#branchModalLabel').text('Tambah Branch');
            form.trigger('reset');
            BranchUI.clearValidation('#branchForm');
            $('#branchId').val('');
            $('#submitForm').html(branchSubmitDefault).prop('disabled', false);
        });

        let branchTable = $('#tableBranch').DataTable(BranchUI.dataTableOptions({
            ajax: {
                url: "{{ route("branch.table") }}",
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
                    render: function(data, type) {
                        if (type !== 'display') return data;

                        return `
                            <span class="branch-code-badge">
                                <i class="mdi mdi-barcode"></i>
                                ${BranchUI.escapeHtml(data)}
                            </span>
                        `;
                    }
                },
                {
                    data: 'name',
                    name: 'name',
                    render: function(data, type, row) {
                        if (type !== 'display') return data;

                        const safeName = BranchUI.escapeHtml(data);
                        const detail = row.address || row.phone || row.email || 'Kontak cabang belum lengkap';

                        return `
                            <div class="branch-identity">
                                <span class="branch-avatar">${BranchUI.getInitials(data)}</span>
                                <div>
                                    <strong title="${safeName}">${safeName}</strong>
                                    <small>${BranchUI.escapeHtml(detail)}</small>
                                </div>
                            </div>
                        `;
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        const isActive = Number(data) === 1;
                        const checked = isActive ? 'checked' : '';
                        const label = isActive ? 'Aktif' : 'Nonaktif';
                        const statusClass = isActive ? 'is-active' : 'is-inactive';

                        return `
                            <div class="branch-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-status"
                                        data-id="${row.id}" ${checked} id="switch${row.id}">
                                    <label class="form-check-label" for="switch${row.id}"></label>
                                </div>
                                <span class="branch-status-text ${statusClass}">${label}</span>
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

        BranchUI.initTableTools({
            table: branchTable,
            tableSelector: '#tableBranch',
            searchSelector: '#branchSearch',
            totalTarget: '#branchTotalCount',
            filteredTarget: '#branchFilteredCount',
            selectedTarget: '#branchSelectedCount'
        });

        $('#tableBranch').on('change', '.toggle-status', function() {
            const $toggle = $(this);
            const branchId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;
            const $statusText = $toggle.closest('.branch-status-wrap').find('.branch-status-text');

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("branch.updateStatus") }}",
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: branchId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status !== 1)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: 'Status branch berhasil diperbarui.',
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

        $('#branchForm').on('submit', function(e) {
            e.preventDefault();
            BranchUI.clearValidation('#branchForm');

            const formData = $(this).serialize();
            const branchId = $('#branchId').val();
            const url = branchId ? "{{ route("branch.update", ":id") }}".replace(':id', branchId) :
                "{{ route("branch.store") }}";
            const method = branchId ? 'PUT' : 'POST';
            const normalLabel = branchId ? branchSubmitUpdate : branchSubmitDefault;

            Swal.fire({
                title: branchId ? 'Perbarui data branch ini?' : 'Tambahkan branch baru?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya, simpan',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                BranchUI.setButtonLoading('#submitForm', true, 'Menyimpan...', normalLabel);

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
                            $('#branchId').val('');
                            branchTable.ajax.reload(null, false);
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.errors;
                            const errorMessages = [];

                            for (let key in errors) {
                                BranchUI.markInvalid(key, errors[key][0]);
                                errorMessages.push(errors[key][0]);
                            }

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
                            return;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: 'Terjadi kesalahan saat menyimpan branch.'
                        });
                    },
                    complete: function() {
                        BranchUI.setButtonLoading('#submitForm', false, 'Menyimpan...', normalLabel);
                    }
                });
            });
        });

        window.editBranch = function(id) {
            const modal = $('#branchModal');
            modal.modal('show');

            $('#branchForm')[0].reset();
            BranchUI.clearValidation('#branchForm');
            $('#branchId').val(id);
            $('#submitForm').html(branchSubmitUpdate).prop('disabled', false);

            $.ajax({
                url: "{{ route("branch.edit", ":id") }}".replace(':id', id),
                method: 'GET',
                success: function(response) {
                    $('#branchModalLabel').text('Edit Branch');
                    $('#code').val(response.code);
                    $('#name').val(response.name);
                    $('#address').val(response.address);
                    $('#phone').val(response.phone);
                    $('#email').val(response.email);
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengambil data branch. Silakan coba lagi.',
                    });
                    modal.modal('hide');
                }
            });
        };

        window.deleteBranch = function(id) {
            Swal.fire({
                title: 'Hapus branch ini?',
                text: 'Branch akan dihapus secara permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("branch.destroy", ":id") }}".replace(':id', id),
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
                            branchTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus branch.', 'error');
                    }
                });
            });
        }
    });
</script>
