<script>
    $(document).ready(function() {
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        const modalSelector = '#obatModal';
        const formSelector = '#obatForm';
        const submitSelector = '#submitObatForm';
        const excelModalSelector = '#obatModalExcell';
        const categorySelector = '[name="category_id"]';
        const mainCategorySelector = '[name="main_category_id"]';
        const subCategorySelector = '[name="sub_kategori_id"]';
        let modalMode = 'create';
        let isHydratingEdit = false;
        let lookupRequest = null;

        if ($.fn.dropify) {
            $('#obatExcelInput').dropify();
        }

        $('.master-obat-select').select2({
            placeholder: function() {
                return $(this).data('placeholder') || 'Pilih data';
            },
            allowClear: true,
            width: '100%',
            dropdownParent: $(modalSelector)
        });

        function normalizeList(response) {
            if (Array.isArray(response)) return response;
            return response && Array.isArray(response.data) ? response.data : [];
        }

        function optionLabel(item) {
            const name = item.nama || item.name || 'Tanpa Nama';
            const code = item.kode || item.code || '';
            return code ? `${code} - ${name}` : name;
        }

        function resetSelect($select) {
            $select.empty().append(new Option('', '', false, false));
            $select.val('').trigger('change.select2');
        }

        function setSelectState($select, isDisabled, label) {
            $select.prop('disabled', isDisabled);
            if (label) {
                $select.empty().append(new Option(label, '', false, false)).trigger('change.select2');
            }
        }

        function loadOptions($select, url, loadingText, emptyText, selectedValue = '') {
            setSelectState($select, true, loadingText);

            return $.ajax({
                url: url,
                type: 'GET',
                dataType: 'json'
            }).then(function(response) {
                const data = normalizeList(response);
                $select.prop('disabled', false).empty().append(new Option('', '', false, false));

                if (!data.length) {
                    $select.append(new Option(emptyText, '', false, false));
                    $select.val('').trigger('change.select2');
                    return data;
                }

                data.forEach(function(item) {
                    $select.append(new Option(optionLabel(item), item.id, false, false));
                });

                if (selectedValue !== null && selectedValue !== undefined && selectedValue !== '') {
                    $select.val(String(selectedValue));
                } else {
                    $select.val('');
                }

                $select.trigger('change.select2');
                return data;
            }, function() {
                $select.prop('disabled', false).empty()
                    .append(new Option('Gagal memuat data', '', false, false))
                    .trigger('change.select2');
                return [];
            });
        }

        function loadMainKategori(categoryId, selectedValue = '') {
            const $main = $(mainCategorySelector);
            const $sub = $(subCategorySelector);
            resetSelect($main);
            resetSelect($sub);
            $sub.prop('disabled', true);

            if (!categoryId) {
                $main.prop('disabled', true);
                return $.Deferred().resolve([]).promise();
            }

            return loadOptions(
                $main,
                "{{ route("masterObat.getMainKategori", ":kategoriUtama") }}".replace(':kategoriUtama', categoryId),
                'Memuat kategori...',
                'Tidak ada kategori tersedia',
                selectedValue
            );
        }

        function loadSubKategori(mainCategoryId, selectedValue = '') {
            const $sub = $(subCategorySelector);
            resetSelect($sub);

            if (!mainCategoryId) {
                $sub.prop('disabled', true);
                return $.Deferred().resolve([]).promise();
            }

            return loadOptions(
                $sub,
                "{{ route("masterObat.getSubKategori", ":kategori") }}".replace(':kategori', mainCategoryId),
                'Memuat sub kategori...',
                'Tidak ada sub kategori tersedia',
                selectedValue
            );
        }

        function loadLookupOptions() {
            const requests = [
                loadOptions($(categorySelector), "{{ route("masterObat.getKategoriUtama") }}", 'Memuat kategori utama...', 'Tidak ada kategori utama tersedia'),
                loadOptions($('[name="sediaan_id"]'), "{{ route("masterObat.getSediaan") }}", 'Memuat sediaan...', 'Tidak ada sediaan tersedia'),
                loadOptions($('[name="golongan_id"]'), "{{ route("masterObat.getGolongan") }}", 'Memuat golongan...', 'Tidak ada golongan tersedia'),
                loadOptions($('[name="satuan_id"]'), "{{ route("masterObat.getSatuan") }}", 'Memuat satuan...', 'Tidak ada satuan tersedia'),
                loadOptions($('[name="pabrikan_id"]'), "{{ route("masterObat.getPabrikan") }}", 'Memuat pabrikan...', 'Tidak ada pabrikan tersedia'),
                loadOptions($('[name="distributor_id"]'), "{{ route("masterObat.getDistributor") }}", 'Memuat distributor...', 'Tidak ada distributor tersedia'),
                loadOptions($('[name="rak_id"]'), "{{ route("masterObat.getRak") }}", 'Memuat rak...', 'Tidak ada rak tersedia')
            ];

            $(mainCategorySelector).prop('disabled', true);
            $(subCategorySelector).prop('disabled', true);

            return $.when.apply($, requests);
        }

        lookupRequest = loadLookupOptions();

        $(categorySelector).on('change', function() {
            if (isHydratingEdit) return;
            loadMainKategori($(this).val());
        });

        $(mainCategorySelector).on('change', function() {
            if (isHydratingEdit) return;
            loadSubKategori($(this).val());
        });

        function resetFormForCreate() {
            const $form = $(formSelector);
            $form[0].reset();
            $('#obatModalLabel').text('Tambah Master Obat');
            $('#obatModalSubtitle').text('Lengkapi identitas, klasifikasi, pemasok, stok minimum, harga beli, dan status obat.');
            $(submitSelector).html('<i class="mdi mdi-content-save-outline"></i>Simpan');
            $('#obatId').val('');
            MasterObatUI.clearValidation(formSelector);
            $('.master-obat-select').val('').trigger('change.select2');
            resetSelect($(mainCategorySelector));
            resetSelect($(subCategorySelector));
            $(mainCategorySelector).prop('disabled', true);
            $(subCategorySelector).prop('disabled', true);
            $('[name="stok_minimum"]').val(0);
            $('[name="is_generik"]').val('1');
            $('[name="is_active"]').val('1');
        }

        $(modalSelector).on('show.bs.modal', function() {
            if (modalMode === 'edit') return;
            resetFormForCreate();
        });

        $(modalSelector).on('hidden.bs.modal', function() {
            modalMode = 'create';
            isHydratingEdit = false;
        });

        const masterObatTable = $('#tableObat').DataTable(MasterObatUI.dataTableOptions({
            ajax: {
                url: "{{ route("masterObat.table") }}",
                type: "GET"
            },
            columns: [{
                    data: null,
                    name: 'detail',
                    orderable: false,
                    searchable: false,
                    className: 'obat-sticky-detail text-center',
                    render: function() {
                        return `
                            <button type="button" class="btn obat-detail-toggle" title="Lihat detail">
                                <i class="mdi mdi-chevron-down"></i>
                            </button>
                        `;
                    }
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'obat-sticky-action text-center'
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'obat-sticky-no text-center'
                },
                {
                    data: 'kode_obat',
                    name: 'kode_obat',
                    render: function(data) {
                        return MasterObatUI.codeBadge(data);
                    }
                },
                {
                    data: 'nama_obat',
                    name: 'nama_obat',
                    render: function(data, type, row) {
                        const subtitle = row.kemasan && row.kemasan !== '-' ? row.kemasan : 'Master obat';
                        return MasterObatUI.identity(data, subtitle);
                    }
                },
                {
                    data: 'category_id',
                    name: 'category_id',
                    render: function(data, type, row) {
                        return MasterObatUI.miniStack(row.category_id, row.main_category_id);
                    }
                },
                {
                    data: 'stok_minimum',
                    name: 'stok_minimum',
                    render: function(data, type, row) {
                        return MasterObatUI.minimumBadge(data);
                    }
                },
                {
                    data: 'harga_beli',
                    name: 'harga_beli',
                    render: function(data, type, row) {
                        return MasterObatUI.moneyBadge(data);
                    }
                },
                {
                    data: 'jenis',
                    name: 'jenis',
                    render: function(data) {
                        return MasterObatUI.typeBadge(data);
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
                            <div class="obat-status-wrap">
                                <div class="form-check form-switch mb-0">
                                    <input type="checkbox" class="form-check-input toggle-obat-status" data-id="${row.id}" ${checked} id="obatSwitch${row.id}">
                                    <label class="form-check-label" for="obatSwitch${row.id}"></label>
                                </div>
                                <span class="obat-status-text ${textClass}">${text}</span>
                            </div>
                        `;
                    },
                    orderable: false,
                    searchable: false
                }
            ]
        }));

        MasterObatUI.initTableTools({
            table: masterObatTable,
            tableSelector: '#tableObat',
            searchSelector: '#obatSearch',
            totalTarget: '#obatTotal',
            filteredTarget: '#obatFiltered',
            selectedTarget: '#obatSelected'
        });

        $('#tableObat tbody').on('click', '.obat-detail-toggle', function(event) {
            event.preventDefault();
            event.stopPropagation();

            const $button = $(this);
            const $row = $button.closest('tr');
            const row = masterObatTable.row($row);

            if (row.child.isShown()) {
                row.child.hide();
                $row.removeClass('shown');
                $button.removeClass('is-open').attr('title', 'Lihat detail');
                return;
            }

            row.child(MasterObatUI.detailPanel(row.data()), 'obat-detail-row').show();
            $row.addClass('shown');
            $button.addClass('is-open').attr('title', 'Tutup detail');
            MasterObatUI.initTooltips();
        });

        $('#tableObat').on('change', '.toggle-obat-status', function() {
            const $toggle = $(this);
            const $statusText = $toggle.closest('.obat-status-wrap').find('.obat-status-text');
            const obatId = $toggle.data('id');
            const status = $toggle.is(':checked') ? 1 : 0;
            const previousStatus = status ? 0 : 1;

            $toggle.prop('disabled', true);

            $.ajax({
                url: "{{ route("masterObat.updateStatus") }}",
                method: 'PUT',
                data: {
                    status: status,
                    id: obatId
                },
                success: function() {
                    $statusText
                        .toggleClass('is-active', status === 1)
                        .toggleClass('is-inactive', status === 0)
                        .text(status === 1 ? 'Aktif' : 'Nonaktif');

                    MasterObatUI.toast('success', 'Status Diperbarui', status === 1 ? 'Obat sekarang aktif.' : 'Obat sekarang nonaktif.');
                },
                error: function() {
                    $toggle.prop('checked', previousStatus === 1);
                    $statusText
                        .toggleClass('is-active', previousStatus === 1)
                        .toggleClass('is-inactive', previousStatus === 0)
                        .text(previousStatus === 1 ? 'Aktif' : 'Nonaktif');

                    MasterObatUI.toast('error', 'Gagal Mengubah Status', 'Terjadi kesalahan saat mengubah status obat.');
                },
                complete: function() {
                    $toggle.prop('disabled', false);
                }
            });
        });

        $(formSelector).on('submit', function(e) {
            e.preventDefault();

            const obatId = $('#obatId').val();
            const url = obatId ?
                "{{ route("masterObat.update", ":id") }}".replace(':id', obatId) :
                "{{ route("masterObat.store") }}";
            const method = obatId ? 'PUT' : 'POST';
            const normalHtml = obatId ?
                '<i class="mdi mdi-content-save-edit-outline"></i>Update' :
                '<i class="mdi mdi-content-save-outline"></i>Simpan';

            MasterObatUI.clearValidation(formSelector);
            MasterObatUI.setButtonLoading(submitSelector, true, obatId ? 'Mengupdate...' : 'Menyimpan...', normalHtml);

            $.ajax({
                url: url,
                method: method,
                data: $(this).serialize(),
                success: function(response) {
                    if (response.status === 'success') {
                        $(modalSelector).modal('hide');
                        MasterObatUI.toast('success', response.message);
                        masterObatTable.ajax.reload(null, false);
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        const messages = MasterObatUI.markFieldErrors(formSelector, xhr.responseJSON.errors);
                        MasterObatUI.toast('error', 'Validasi Gagal', messages.join('<br>'));
                        return;
                    }

                    MasterObatUI.toast('error', 'Gagal Menyimpan', 'Terjadi kesalahan saat menyimpan master obat.');
                },
                complete: function() {
                    MasterObatUI.setButtonLoading(submitSelector, false, '', normalHtml);
                }
            });
        });

        function setInputValues(response) {
            [
                'kode_obat',
                'nama_obat',
                'komposisi',
                'indikasi',
                'dosis',
                'kemasan',
                'stok_minimum',
                'harga_beli'
            ].forEach(function(field) {
                $(`[name="${field}"]`).val(response[field] ?? '');
            });

            $('[name="is_generik"]').val(String(Number(response.is_generik ?? 1)));
            $('[name="is_active"]').val(String(Number(response.is_active ?? 1)));
        }

        function setSelectValue(selector, value) {
            $(selector).val(value ? String(value) : '').trigger('change.select2');
        }

        window.editMasterObat = function(id) {
            modalMode = 'edit';

            $.ajax({
                url: "{{ route("masterObat.edit", ":id") }}".replace(':id', id),
                type: "GET",
                dataType: "json",
                success: function(response) {
                    $(modalSelector).modal('show');
                    $('#obatModalLabel').text('Edit Master Obat');
                    $('#obatModalSubtitle').text('Perbarui identitas, klasifikasi, pemasok, stok minimum, harga beli, dan status obat.');
                    $(submitSelector).html('<i class="mdi mdi-content-save-edit-outline"></i>Update');
                    $('#obatId').val(response.id);
                    MasterObatUI.clearValidation(formSelector);
                    setInputValues(response);

                    lookupRequest.always(function() {
                        isHydratingEdit = true;
                        setSelectValue('[name="sediaan_id"]', response.sediaan_id);
                        setSelectValue('[name="golongan_id"]', response.golongan_id);
                        setSelectValue('[name="satuan_id"]', response.satuan_id);
                        setSelectValue('[name="pabrikan_id"]', response.pabrikan_id);
                        setSelectValue('[name="distributor_id"]', response.distributor_id);
                        setSelectValue('[name="rak_id"]', response.rak_id);
                        setSelectValue(categorySelector, response.category_id);

                        loadMainKategori(response.category_id, response.main_category_id)
                            .always(function() {
                                loadSubKategori(response.main_category_id, response.sub_kategori_id)
                                    .always(function() {
                                        isHydratingEdit = false;
                                    });
                            });
                    });
                },
                error: function() {
                    modalMode = 'create';
                    MasterObatUI.toast('error', 'Gagal Memuat', 'Data master obat tidak bisa dimuat.');
                }
            });
        };

        window.deleteMasterObat = function(id) {
            Swal.fire({
                title: 'Hapus master obat?',
                text: 'Data yang sudah dihapus tidak bisa dikembalikan.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (!result.isConfirmed) return;

                $.ajax({
                    url: "{{ route("masterObat.destroy", ":id") }}".replace(':id', id),
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            MasterObatUI.toast('success', 'Berhasil Dihapus', response.message);
                            masterObatTable.ajax.reload(null, false);
                            return;
                        }

                        Swal.fire('Gagal', response.message, 'error');
                    },
                    error: function() {
                        Swal.fire('Gagal', 'Terjadi kesalahan saat menghapus master obat.', 'error');
                    }
                });
            });
        };

        function resetObatExcelForm() {
            $('#obatExcelForm')[0].reset();
            const dropify = $('#obatExcelInput').data('dropify');

            if (dropify) {
                dropify.resetPreview();
                dropify.clearElement();
            }
        }

        $(excelModalSelector).on('hidden.bs.modal', resetObatExcelForm);

        $('#obatDownloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("masterObat.exportTemplate") }}";
        });

        $('#obatSubmitExcel').on('click', function() {
            const fileInput = $('#obatExcelInput')[0];
            const file = fileInput.files[0];
            const normalHtml = '<i class="mdi mdi-upload"></i>Upload';

            if (!file) {
                $('#obatExcelInput').closest('.dropify-wrapper').addClass('shake border-danger');
                setTimeout(function() {
                    $('#obatExcelInput').closest('.dropify-wrapper').removeClass('shake border-danger');
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
            MasterObatUI.setButtonLoading('#obatSubmitExcel', true, 'Mengupload...', normalHtml);

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
                url: "{{ route("masterObat.import") }}",
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
                            timer: 2800,
                            showConfirmButton: false,
                            willClose: function() {
                                $(excelModalSelector).modal('hide');
                                masterObatTable.ajax.reload(null, false);
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
                    MasterObatUI.setButtonLoading('#obatSubmitExcel', false, '', normalHtml);
                }
            });
        });
    });
</script>
