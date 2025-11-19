<script>
    $(document).ready(function() {

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        // --- Template Row
        function getKonversiRow() {
            return `
                    <div class="p-3 border rounded-3 mb-3 shadow-sm bg-light input-group-item">

                        <div class="row g-3">

                            <!-- Obat -->
                            <div class="col-md-4">
                                <label class="form-label small fw-bold text-secondary">
                                    <i class="mdi mdi-pill me-1"></i> Obat
                                </label>
                                <select class="js-example-basic-single form-select obatSelect" data-width="100%" name="obat_id[]" required>
                                    <option value="">-- Pilih Obat --</option>
                                </select>
                            </div>

                            <!-- Satuan -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">
                                    <i class="mdi mdi-package-variant me-1"></i> Satuan Pembelian
                                </label>
                                <select class="form-select satuanSelect" name="satuan_id[]" required>
                                    <option value="">Memuat data...</option>
                                </select>
                            </div>

                            <!-- Konversi -->
                            <div class="col-md-3">
                                <label class="form-label small fw-bold text-secondary">
                                    <i class="mdi mdi-arrow-collapse-vertical me-1"></i> Konversi ke PCS
                                </label>
                                <input class="form-control" type="number" name="konversi[]" min="1" placeholder="Contoh: 10" required>
                            </div>

                            <!-- Default -->
                            <div class="col-md-2">
                                <label class="form-label small fw-bold text-secondary d-block">
                                    <i class="mdi mdi-check-circle-outline me-1"></i> Default
                                </label>

                                <div class="form-check mt-1">
                                    <!-- Hidden field untuk nilai default -->
                                    <input type="hidden" name="is_default[]" value="0" class="defaultHidden">

                                    <!-- Checkbox TANPA name -->
                                    <input class="form-check-input defaultCheck" type="checkbox" value="1">

                                    <label class="form-check-label">Jadikan Default</label>
                                </div>
                            </div>


                        </div>

                        <div class="text-end mt-3">
                            <button type="button" class="btn btn-outline-danger btn-sm remove-input">
                                <i class="mdi mdi-trash-can-outline me-1"></i> Hapus Baris
                            </button>
                        </div>

                    </div>
                `;
        }

        // --- Ambil Data Obat
        function loadObatInto($select, selectedId = null) {
            $.ajax({
                url: "{{ route("konversiSatuanObat.getObat") }}",
                type: "GET",
                success: function(data) {
                    $select.empty().append('<option value="">-- Pilih Obat --</option>');

                    data.forEach(item => {
                        $select.append(new Option(item.nama_obat, item.id, false, false));
                    });

                    // re-init select2
                    $select.select2({
                        dropdownParent: $('#konversiModal')
                    });

                    if (selectedId) {
                        $select.val(selectedId).trigger('change');
                    }
                }
            });
        }

        // --- Ambil Data Satuan
        function loadSatuanInto($select, selectedId = null) {
            $.ajax({
                url: "{{ route("konversiSatuanObat.getSatuan") }}",
                type: "GET",
                success: function(data) {
                    $select.empty().append('<option value="">-- Pilih Satuan --</option>');

                    data.forEach(item => {
                        $select.append(new Option(item.nama, item.id, false, false));
                    });

                    // re-init select2
                    $select.select2({
                        dropdownParent: $('#konversiModal')
                    });

                    if (selectedId) {
                        $select.val(selectedId).trigger('change');
                    }
                }
            });
        }

        // --- Default Checkbox
        $(document).on('change', '.defaultCheck', function() {
            let hidden = $(this).closest('.form-check').find('.defaultHidden');

            if ($(this).is(':checked')) {
                hidden.val(1);
            } else {
                hidden.val(0);
            }
        });


        // --- Reset modal ketika dibuka
        $('#konversiModal').on('show.bs.modal', function() {

            let form = $('#konversiForm');
            $('#konversiModalLabel').text('Tambah Konversi Satuan Obat');
            form.trigger('reset');
            $('#submitForm').text('Simpan');
            $('#addInput').show();

            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#konversiId').val('');

            // Buat row terlebih dahulu
            $('#input-wrapper').html(getKonversiRow());

            // Setelah row ada, baru load satuan
            loadObatInto($('#input-wrapper .obatSelect'));
            loadSatuanInto($('#input-wrapper .satuanSelect'));

        });

        // --- Tambah input baru
        $(document).on('click', '#addInput', function() {

            let newRow = $(getKonversiRow());
            $('#input-wrapper').append(newRow);

            // Load satuan untuk row baru
            loadObatInto(newRow.find('.obatSelect'));
            loadSatuanInto(newRow.find('.satuanSelect'));
        });

        // --- Hapus input tertentu
        $(document).on('click', '.remove-input', function() {
            $(this).closest('.input-group-item').remove();
        });

        // --- DataTable
        let konversiTable = $('#tableKonversi').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("konversiSatuanObat.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'obat_id',
                    name: 'obat_id'
                },
                {
                    data: 'satuan_id',
                    name: 'satuan_id'
                },
                {
                    data: 'konversi',
                    name: 'konversi'
                },
                {
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // --- Hilangkan search default bawaan DataTables
        $('.dataTables_filter').hide();

        // --- Hubungkan search custom dengan DataTables
        $('#searchKonversi').on('keyup', function() {
            konversiTable.search(this.value).draw();
        });

        // --- Submit form
        $('#konversiForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let konversiId = $('#konversiId').val();

            let url = konversiId ?
                "{{ route("konversiSatuanObat.update", ":id") }}".replace(':id', konversiId) :
                "{{ route("konversiSatuanObat.store") }}";

            let method = konversiId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#konversiModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#konversiForm')[0].reset();
                        $('#konversiId').val('');
                        konversiTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#golonganForm').find('.invalid-feedback').text('');
                        $('#golonganForm').find('.form-control').removeClass(
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

        // --- EDIT KONVERSI SATUAN
        window.editKonversiSatuanObat = function(id) {

            $.ajax({
                url: "{{ route("konversiSatuanObat.edit", ":id") }}".replace(":id", id),
                type: "GET",
                success: function(res) {

                    console.log(res);

                    // Show modal
                    $('#konversiModal').modal('show');
                    $('#konversiModalLabel').text('Edit Konversi Satuan Obat');
                    $('#submitForm').text('Update');

                    // Nonaktifkan tombol tambah row (edit hanya 1 row)
                    $('#addInput').hide();

                    // Set hidden ID
                    $('#konversiId').val(res.id);

                    // Render row isi data
                    $('#input-wrapper').html(`
                            <div class="p-3 border rounded-3 mb-3 shadow-sm bg-light input-group-item">

                                <div class="row g-3">

                                    <!-- Obat -->
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold text-secondary">
                                            <i class="mdi mdi-pill me-1"></i> Obat
                                        </label>
                                        <select class="form-select obatSelect" name="obat_id" required>
                                            <option value="">Loading...</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <!-- Satuan -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-secondary">
                                            <i class="mdi mdi-package-variant me-1"></i> Satuan Pembelian
                                        </label>
                                        <select class="form-select satuanSelect" name="satuan_id" required>
                                            <option value="">Loading...</option>
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <!-- Konversi -->
                                    <div class="col-md-3">
                                        <label class="form-label small fw-bold text-secondary">
                                            <i class="mdi mdi-arrow-collapse-vertical me-1"></i> Konversi ke PCS
                                        </label>
                                        <input class="form-control" type="number" name="konversi" 
                                            value="${res.konversi}" min="1" required>
                                        <div class="invalid-feedback"></div>
                                    </div>

                                    <!-- Default -->
                                    <div class="col-md-2">
                                        <label class="form-label small fw-bold text-secondary d-block">
                                            <i class="mdi mdi-check-circle-outline me-1"></i> Default
                                        </label>

                                        <div class="form-check mt-1">
                                            <!-- Hidden field untuk nilai default -->
                                            <input type="hidden" name="is_default" value="0" class="defaultHidden">

                                            <!-- Checkbox TANPA name -->
                                            <input class="form-check-input defaultCheck" type="checkbox" value="1" ${res.is_default == 1 ? 'checked' : ''}>

                                            <label class="form-check-label">Jadikan Default</label>
                                        </div>
                                    </div>

                                </div>

                            </div>
                        `);

                    // ===================================================
                    // LOAD OBAT & SELECT VALUE
                    // ===================================================
                    loadObatInto($('.obatSelect'), res.obat_id);

                    // ===================================================
                    // LOAD SATUAN & SELECT VALUE
                    // ===================================================
                    loadSatuanInto($('.satuanSelect'), res.satuan_id);
                }
            });
        };

        // --- Hapus
        window.deleteKonversiSatuanObat = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Konversi ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("konversiSatuanObat.destroy", ":id") }}".replace(
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
                                konversiTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Konversi.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // --- Download Template
        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("konversiSatuanObat.exportTemplate") }}";
        })

        // --- submitFormExcell
        $('#submitFormExcell').on('click', function() {
            let fileInput = $('#myDropify')[0];
            let file = fileInput.files[0];

            // Jika file belum dipilih
            if (!file) {
                // Tambahkan efek getar (shake)
                $('#myDropify').addClass('shake border-danger');

                // Hilangkan efek setelah 600ms
                setTimeout(() => {
                    $('#myDropify').removeClass('shake border-danger');
                }, 600);

                // Tampilkan alert
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: 'Silakan pilih file Excel terlebih dahulu!',
                });

                return; // hentikan eksekusi selanjutnya
            }

            let formData = new FormData();
            formData.append('file', file);

            // Alert progress
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
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Kirim AJAX
            $.ajax({
                xhr: function() {
                    let xhr = new window.XMLHttpRequest();
                    xhr.upload.addEventListener("progress", function(evt) {
                        if (evt.lengthComputable) {
                            let percentComplete = Math.round((evt.loaded / evt
                                .total) * 100);
                            $('#uploadProgressBar')
                                .css('width', percentComplete + '%')
                                .text(percentComplete + '%');
                        }
                    }, false);
                    return xhr;
                },
                url: "{{ route("konversiSatuanObat.import") }}",
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    Swal.close();
                    if (response.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati (sudah ada).</p>
                            `,
                            timer: 2500,
                            showConfirmButton: false,
                            willClose: () => {
                                // Tutup modal
                                $('#konversiModalExcell').modal('hide');

                                // Reload DataTable jika sudah diinisialisasi
                                if (typeof konversiTable !== 'undefined') {
                                    konversiTable.ajax.reload(null,
                                        false
                                    ); // false = tetap di halaman sekarang
                                }
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: response.message ||
                                'Terjadi kesalahan saat import data.',
                        });
                    }
                },
                error: function(xhr) {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Gagal mengupload file: ' + xhr.responseText,
                    });
                }
            });
        });


    });
</script>
