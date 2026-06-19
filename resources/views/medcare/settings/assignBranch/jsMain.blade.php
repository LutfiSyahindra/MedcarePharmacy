<script>
    $(document).ready(function() {
        const assignBranchDefault = '<i class="mdi mdi-account-check-outline"></i>Simpan Assignment';

        function loadUserOptions(selectedIds = []) {
            const normalizedSelected = selectedIds.map(String);

            return $.ajax({
                url: '{{ route("assignBranch.getUser") }}',
                type: 'GET'
            }).done(function(response) {
                const userSelect = $('#userSelect');

                if (userSelect.hasClass('select2-hidden-accessible')) {
                    userSelect.select2('destroy');
                }

                userSelect.empty();
                response.forEach(function(user) {
                    const isSelected = normalizedSelected.includes(String(user.id)) ? 'selected' : '';
                    userSelect.append(
                        `<option value="${user.id}" ${isSelected}>${BranchUI.escapeHtml(user.name)}</option>`
                    );
                });

                userSelect.select2({
                    dropdownParent: $('#assignBranchModal'),
                    placeholder: "Pilih user",
                    allowClear: true,
                    width: '100%'
                });

                userSelect.off('change.branchUsers').on('change.branchUsers', function() {
                    BranchUI.updateSelectCount('#userSelect', '#userSelectionCount');
                });

                BranchUI.updateSelectCount('#userSelect', '#userSelectionCount');
            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'Gagal memuat data user.'
                });
            });
        }

        let assignBranchTable = $('#tableAssignBranch').DataTable(BranchUI.dataTableOptions({
            ajax: {
                url: "{{ route("assignBranch.table") }}",
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
                        return `
                            <div class="branch-identity">
                                <span class="branch-avatar">${BranchUI.getInitials(data)}</span>
                                <div>
                                    <strong title="${safeName}">${safeName}</strong>
                                    <small>Branch ID #${BranchUI.escapeHtml(row.id)}</small>
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

        BranchUI.initTableTools({
            table: assignBranchTable,
            tableSelector: '#tableAssignBranch',
            searchSelector: '#assignBranchSearch',
            totalTarget: '#assignBranchTotalCount',
            filteredTarget: '#assignBranchFilteredCount',
            selectedTarget: '#assignBranchSelectedCount'
        });

        window.assignBranch = function(branchId) {
            const modal = $('#assignBranchModal');
            modal.modal('show');
            $('#assignBranchModalLabel').text('Assign Branch');
            $('#branchId').val(branchId);
            $('#assignBranchSubmit').html(assignBranchDefault).prop('disabled', false);
            $('#userSelectionCount').text('0');

            const optionsRequest = loadUserOptions([]);

            $.ajax({
                url: "{{ route("assignBranch.getAssignedUsers", ":branch") }}".replace(':branch', branchId),
                type: "GET",
                success: function(res) {
                    optionsRequest.done(function() {
                        const selectedIds = (res || []).map(function(user) {
                            return String(user.id);
                        });
                        $('#userSelect').val(selectedIds).trigger('change');
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data user assign.'
                    });
                }
            });
        };

        $('#selectAllUsers').on('click', function() {
            $('#userSelect option').prop('selected', true);
            $('#userSelect').trigger('change');
        });

        $('#clearUsersSelection').on('click', function() {
            $('#userSelect').val(null).trigger('change');
        });

        $('#assignBranchForm').on('submit', function(e) {
            e.preventDefault();

            const formData = $(this).serialize();
            BranchUI.setButtonLoading('#assignBranchSubmit', true, 'Menyimpan...', assignBranchDefault);

            $.ajax({
                url: "{{ route("assignBranch.assign") }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#assignBranchModal').modal('hide');
                        $('#assignBranchForm')[0].reset();
                        $('#userSelect').val(null).trigger('change');
                        assignBranchTable.ajax.reload(null, false);

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
                        text: xhr.status === 422 ? 'Pilih minimal satu user.' : 'Terjadi kesalahan server.'
                    });
                },
                complete: function() {
                    BranchUI.setButtonLoading('#assignBranchSubmit', false, 'Menyimpan...', assignBranchDefault);
                }
            });
        });
    });
</script>
