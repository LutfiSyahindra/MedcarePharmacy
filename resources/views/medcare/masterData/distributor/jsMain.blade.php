<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#distributorModal';
        const formSelector = '#distributorForm';
        const wrapperSelector = '#distributorInputWrapper';
        const submitSelector = '#submitDistributorForm';
        const addButtonSelector = '#addDistributorInput';

        if ($.fn.dropify) {
            $('#distributorExcelInput').dropify();
        }

        function emptyIfDash(value) {
            return value && value !== '-' ? value : '';
        }

        function distributorRow(mode = 'create', data = {}) {
            const isEdit = mode === 'edit';
            const suffix = isEdit ? '' : '[]';
            const kodeValue = CompanyUI.escapeHtml(emptyIfDash(data.kode));
            const namaValue = CompanyUI.escapeHtml(emptyIfDash(data.nama));
            const alamatValue = CompanyUI.escapeHtml(emptyIfDash(data.alamat));
            const teleponValue = CompanyUI.escapeHtml(emptyIfDash(data.telepon));
            const emailValue = CompanyUI.escapeHtml(emptyIfDash(data.email));

            return `
                <div class="company-batch-row">
                    <div class="company-field is-code">
                        <label class="form-label">Kode</label>
                        <div class="company-input-shell">
                            <span class="company-input-icon"><i class="mdi mdi-pound"></i></span>
                            <input class="form-control" name="kode${suffix}" type="text" value="${kodeValue}" placeholder="DST">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="company-field is-name">
                        <label class="form-label">Distributor</label>
                        <div class="company-input-shell">
                            <span class="company-input-icon"><i class="mdi mdi-archive"></i></span>
                            <input class="form-control" name="nama${suffix}" type="text" value="${namaValue}" placeholder="Contoh: Distributor Sentosa">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="company-field is-address">
                        <label class="form-label">Alamat</label>
                        <div class="company-input-shell">
                            <span class="company-input-icon"><i class="mdi mdi-map-marker-outline"></i></span>
                            <input class="form-control" name="alamat${suffix}" type="text" value="${alamatValue}" placeholder="Alamat lengkap">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="company-field is-phone">
                        <label class="form-label">Telepon</label>
                        <div class="company-input-shell">
                            <span class="company-input-icon"><i class="mdi mdi-phone-outline"></i></span>
                            <input class="form-control" name="telepon${suffix}" type="text" value="${teleponValue}" placeholder="08123456789" pattern="[0-9]*" inputmode="numeric" maxlength="15">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="company-field is-email">
                        <label class="form-label">Email</label>
                        <div class="company-input-shell">
                            <span class="company-input-icon"><i class="mdi mdi-email-outline"></i></span>
                            <input class="form-control" name="email${suffix}" type="email" value="${emailValue}" placeholder="email@domain.com">
                        </div>
                        <div class="invalid-feedback"></div>
                    </div>
                    ${isEdit ? '' : `
                        <div class="company-field is-action">
                            <button type="button" class="btn btn-outline-danger btn-sm company-row-remove remove-distributor-input">
                                <i class="mdi mdi-delete-outline"></i>
                                Hapus Baris
                            </button>
                        </div>
                    `}
                </div>
            `;
        }

        function resetAddMode() {
            const $form = $(formSelector);

            $('#distributorModalLabel').text('Tambah Distributor');
            $('#distributorModalSubtitle').text('Buat satu atau beberapa data distributor obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#distributorId').val('');
            $('#distributorBatchToolbar').show();
            $form.trigger('reset');
            CompanyUI.clearValidation(formSelector);
            $(wrapperSelector).html(distributorRow());
            CompanyUI.updateBatchCount(wrapperSelector, '#distributorRowCount');
        }

        $(modalSelector).on('show.bs.modal', resetAddMode);

        $(document).on('click', addButtonSelector, function() {
            $(wrapperSelector).append(distributorRow());
            CompanyUI.updateBatchCount(wrapperSelector, '#distributorRowCount');
        });

        $(document).on('click', '.remove-distributor-input', function() {
            if ($(wrapperSelector).find('.company-batch-row').length <= 1) {
                $(this).closest('.company-batch-row').find('input').val('');
                CompanyUI.toast('info', 'Baris dibersihkan', 'Minimal satu baris input tetap tersedia.');
                return;
            }

            $(this).closest('.company-batch-row').remove();
            CompanyUI.updateBatchCount(wrapperSelector, '#distributorRowCount');
        });

        const distributorTable = $('#tableDistributor').DataTable(CompanyUI.dataTableOptions({
            ajax: {
                url: "{{ route("distributor.table") }}",
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
                    name: 'kode',
                    render: function(data) {
                        return CompanyUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama',
                    name: 'nama',
                    render: function(data) {
                        return CompanyUI.identity(data, 'Distributor obat');
                    }
                },
                {
                    data: 'alamat',
                    name: 'alamat',
                    render: function(data) {
                        return CompanyUI.contactBadge(data, 'mdi-map-marker-outline');
                    }
                },
                {
                    data: 'telepon',
                    name: 'telepon',
                    render: function(data) {
                        return CompanyUI.contactBadge(data, 'mdi-phone-outline');
                    }
                },
                {
                    data: 'email',
                    name: 'email',
                    render: function(data) {
                        return CompanyUI.contactBadge(data, 'mdi-email-outline');
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        const checked = data == 1 ? 'checked' : '';
                        const textClass = data == 1 ? 'is-active' : 'is-inactive';
                        const text = data == 1 ? 'Aktif' : 'Nonaktif';

                        return `
                            <div class="company-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-distributor-status" data-id="${row.id}" ${checked} id="distributorSwitch${row.id}">
                                    <label class="form-check-label" for="distributorSwitch${row.id}"></label>
                                </div>
                                <span class="company-status-text ${textClass}">${text}</span>
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

        CompanyUI.initTableTools({
            table: distributorTable,
            tableSelector: '#tableDistributor',
            searchSelector: '#distributorSearch',
            totalTarget: '#distributorTotal',
            filteredTarget: '#distributorFiltered',
            selectedTarget: '#distributorSelected'
        });

        $('#tableDistributor').on('change', '.toggle-distributor-status', function() {
            const $toggle = $(this);
            const $statusText = $toggle.closest('.company-status-wrap').find('.company-status-text');
            const distributorId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("distributor.updateStatus") }}",
                method: 'PUT',
                data: {
                    status: status,
                    id: distributorId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status === 0)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    CompanyUI.toast('success', 'Status Diperbarui', status === 1 ? 'Distributor sekarang aktif.' : 'Distributor sekarang nonaktif.');
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus === 0)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    CompanyUI.toast('error', 'Gagal Mengubah Status', 'Terjadi kesalahan saat mengubah status distributor.');
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const distributorId = $('#distributorId').val();
            const url = distributorId ?
                "{{ route("distributor.update", ":id") }}".replace(':id', distributorId) :
                "{{ route("distributor.store") }}";
            const method = distributorId ? 'PUT' : 'POST';
            const normalHtml = distributorId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            CompanyUI.clearValidation(formSelector);
            CompanyUI.setButtonLoading(submitSelector, true, distributorId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        CompanyUI.toast('success', response.message);
                        distributorTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = CompanyUI.markBatchErrors(formSelector, wrapperSelector, xhr.responseJSON.errors);
                        CompanyUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    CompanyUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan distributor.');
                },
                complete: function() {
                    CompanyUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        window.editDistributor = function(id) {
            $.ajax({
                url: "{{ route("distributor.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#distributorModalLabel').text('Edit Distributor');
                    $('#distributorModalSubtitle').text('Perbarui profil dan kontak distributor yang dipilih.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#distributorBatchToolbar').hide();
                    $('#distributorId').val(response.id);
                    $(wrapperSelector).html(distributorRow('edit', response));
                    CompanyUI.clearValidation(formSelector);
                    CompanyUI.updateBatchCount(wrapperSelector, '#distributorRowCount');
                },
                error: function() {
                    CompanyUI.toast('error', 'Gagal Memuat', 'Data distributor tidak bisa dimuat.');
                }
            });
        };

        window.deleteDistributor = function(id) {
            Swal.fire({
                title: 'Hapus distributor?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("distributor.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            CompanyUI.toast('success', 'Berhasil Dihapus', response.message);
                            distributorTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus distributor.', 'error');
                    }
                });
            });
        };

        function resetDistributorExcelForm() {
            $('#distributorExcelForm')[0].reset();
            const dropify = $('#distributorExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $('#distributorModalExcell').on('hidden.bs.modal', resetDistributorExcelForm);

        $('#distributorDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("distributor.exportTemplate") }}";
        });

        $('#distributorSubmitExcel').on('click', function() {
            const fileInput = $('#distributorExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#distributorExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#distributorExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
                }, 600);

                Swal.fire({
                    icon: 'warning',
                    title: 'File belum dipilih',
                    text: 'Silakan pilih file Excel terlebih dahulu.'
                });
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            CompanyUI.setButtonLoading('#distributorSubmitExcel', true, 'Mengupload...', normalHtml);

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
                didOpen: function() {
                    Swal.showLoading();
                }
            });

            $.ajax({
                xhr: function() {
                    const xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener('progress', function(evt) {
                        if (evt.lengthComputable) {
                            const percentComplete = Math.round((evt.loaded / evt.total) * 100);
                            $('#uploadProgressBar').css('width', percentComplete + '%').text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: "{{ route("distributor.import") }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Import Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati.</p>
                            `,
                            timer: 2600,
                            showConfirmButton: false,
                            willClose: function() {
                                $('#distributorModalExcell').modal('hide');
                                distributorTable.ajax.reload(null, false);
                            }
                        });
                        return;
                    }

                    Swal.fire('Gagal', response.message || 'Terjadi kesalahan saat import data.', 'error');
                },
                error: function(xhr) {
                    Swal.close();
                    const message = xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Gagal mengupload file.';
                    Swal.fire('Gagal Import', message, 'error');
                },
                complete: function() {
                    CompanyUI.setButtonLoading('#distributorSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
