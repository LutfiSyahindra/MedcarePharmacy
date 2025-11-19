<script>
    $(document).ready(function() {

        function loadUserOptions(selectedIds = []) {
            return $.ajax({
                url: '{{ route("assignBranch.getUser") }}',
                type: 'GET',
                success: function(response) {
                    const userSelect = $('#userSelect');
                    userSelect.empty();

                    // Tambah opsi satu per satu
                    response.forEach(function(user) {
                        const isSelected = selectedIds.includes(user.id.toString()) ?
                            'selected' : '';
                        userSelect.append(
                            `<option value="${user.id}" ${isSelected}>${user.name}</option>`
                        );
                    });

                    // Re-init select2
                    userSelect.select2({
                        dropdownParent: $('#assignBranchModal'),
                        placeholder: "Pilih User",
                        allowClear: true,
                        width: '100%'
                    });
                },
                error: function() {
                    alert('Gagal memuat data user!');
                }
            });
        }

        let assignBranchTable = $('#tableAssignBranch').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("assignBranch.table") }}", // pastikan route ini ada
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

        window.assignBranch = function(branchId) {
            const modal = $('#assignBranchModal');
            modal.modal('show');
            $('#assignBranchModalLabel').text('ASSIGN BRANCH');
            $('#submitForm').text('Update');

            // set branch id ke hidden input
            $('#branchId').val(branchId);

            // kosongkan select2 dulu
            $('#userSelect').val(null).trigger('change');
            loadUserOptions([]);

            // ambil data user yang sudah assign ke branch ini
            $.ajax({
                url: "{{ route("assignBranch.getAssignedUsers", ":branch") }}".replace(':branch',
                    branchId),
                type: "GET",
                success: function(res) {
                    res.forEach(function(user) {
                        // kalau option sudah ada, tinggal select
                        if ($('#userSelect').find("option[value='" + user.id + "']")
                            .length) {
                            let selected = $('#userSelect').val() || [];
                            selected.push(user.id.toString());
                            $('#userSelect').val(selected).trigger('change');
                        } else {
                            // kalau option belum ada, tambahin
                            var newOption = new Option(user.name, user.id, true, true);
                            $('#userSelect').append(newOption).trigger('change');
                        }
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data user assign!'
                    });
                }
            });
        };

        $('#assignBranchForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();

            $.ajax({
                url: "{{ route("assignBranch.assign") }}",
                type: "POST",
                data: formData,
                success: function(response) {
                    if (response.success) {
                        $('#assignBranchModal').modal('hide');
                        $('#assignBranchForm')[0].reset();
                        $('#userSelect').val(null).trigger('change');
                        $('#tableAssignBranch').DataTable().ajax.reload();

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: response.message
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message
                        });
                    }
                },
                error: function(xhr) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Terjadi kesalahan server!'
                    });
                }
            });
        });

    });
</script>
