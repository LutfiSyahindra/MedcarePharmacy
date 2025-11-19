<script>
    $(document).ready(function() {

        // --- Variabel select2
        let kategoriSelect = $('select[name="main_category_id"]');
        let subKategoriSelect = $('#subKategori');
        let SediaanSelect = $('select[name="sediaan_id"]');
        let GolonganSelect = $('select[name="golongan_id"]');
        let SatuanSelect = $('select[name="satuan_id"]');
        let PabrikanSelect = $('select[name="pabrikan_id"]');
        let DistributorSelect = $('select[name="distributor_id"]');
        let RakSelect = $('select[name="rak_id"]');
        let kategoriUtamaSelect = $('select[name="category_id"]');
        let isEditMode = false;

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });

        // --- Inisialisasi Select2
        $('.js-example-basic-single').select2({
            placeholder: "-- Pilih --",
            allowClear: false,
            width: 'resolve',
            dropdownParent: $('#obatModal')
        });

        // --- Reset modal ketika dibuka
        $('#obatModal').on('show.bs.modal', function() {
            let form = $('#obatForm');
            $('#obatModalLabel').text('ADD MASTER OBAT');
            form.trigger('reset');
            $('#submitForm').text('Add');
            $('#addInput').show();

            // reset error message
            form.find('.invalid-feedback').text('');
            form.find('.form-control').removeClass('is-invalid');
            $('#obatId').val('');

            // --- Reset dropdown select2
            $('.js-example-basic-single').val('').trigger('change');
        });

        // --- Ambil Data Kategori Utama
        kategoriUtamaSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        if (isEditMode) return;
        $.ajax({
            url: "{{ route("masterObat.getKategoriUtama") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                kategoriUtamaSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Kategori --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    kategoriUtamaSelect.append(
                        '<option value="">Tidak ada kategori utama tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    kategoriUtamaSelect.append(option);
                });

                kategoriUtamaSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error kategori:', xhr);
                kategoriUtamaSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori utama</option>');
            }
        });

        // --- Pemilihan Kategori Berdasarkan Kategori Utama
        kategoriUtamaSelect.on('change', function() {
            if (isEditMode) return;
            let kategoriUtamaId = $(this).val();

            kategoriSelect.empty().append('<option value="">-- Pilih Sub Kategori --</option>');

            if (!kategoriUtamaId) return; // jika kategori kosong, hentikan

            kategoriSelect.prop('disabled', true)
                .append('<option>Memuat data...</option>');

            $.ajax({
                url: "{{ route("masterObat.getMainKategori", ":kategoriUtama") }}".replace(
                    ':kategoriUtama', kategoriUtamaId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    kategoriSelect.prop('disabled', false).empty()
                        .append('<option value="">-- Pilih Sub Kategori --</option>');

                    if (response.length === 0) {
                        kategoriSelect.append(
                            '<option value="">Tidak ada main kategori tersedia</option>'
                        );
                        return;
                    }

                    response.forEach(item => {
                        let nama = item.nama || item.name || 'Tanpa Nama';
                        kategoriSelect.append(
                            `<option value="${item.id}">${nama}</option>`);
                    });

                    kategoriSelect.trigger('change.select2');
                },
                error: function() {
                    kategoriSelect.prop('disabled', false)
                        .empty()
                        .append(
                            '<option value="">Gagal memuat data main kategori</option>');
                }
            });
        });

        // --- Pemilihan Sub Kategori Berdasarkan Kategori
        kategoriSelect.on('change', function() {
            if (isEditMode) return;
            let kategoriId = $(this).val();

            subKategoriSelect.empty().append('<option value="">-- Pilih Sub Kategori --</option>');

            if (!kategoriId) return; // jika kategori kosong, hentikan

            subKategoriSelect.prop('disabled', true)
                .append('<option>Memuat data...</option>');

            $.ajax({
                url: "{{ route("masterObat.getSubKategori", ":kategori") }}".replace(
                    ':kategori', kategoriId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    subKategoriSelect.prop('disabled', false).empty()
                        .append('<option value="">-- Pilih Sub Kategori --</option>');

                    if (response.length === 0) {
                        subKategoriSelect.append(
                            '<option value="">Tidak ada sub kategori tersedia</option>');
                        return;
                    }

                    response.forEach(item => {
                        let nama = item.nama || item.name || 'Tanpa Nama';
                        subKategoriSelect.append(
                            `<option value="${item.id}">${nama}</option>`);
                    });

                    subKategoriSelect.trigger('change.select2');
                },
                error: function() {
                    subKategoriSelect.prop('disabled', false)
                        .empty()
                        .append('<option value="">Gagal memuat data sub kategori</option>');
                }
            });
        });

        // --- Sediaan Select
        SediaanSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getSediaan") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                SediaanSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Sediaan --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    SediaanSelect.append('<option value="">Tidak ada Sediaan tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    SediaanSelect.append(option);
                });

                SediaanSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Sediaan:', xhr);
                SediaanSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Sediaan Select
        GolonganSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getGolongan") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                GolonganSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Golongan --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    GolonganSelect.append('<option value="">Tidak ada Golongan tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    GolonganSelect.append(option);
                });

                GolonganSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Golongan:', xhr);
                GolonganSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Pabrikan Select
        PabrikanSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getPabrikan") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                PabrikanSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Pabrikan --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    PabrikanSelect.append('<option value="">Tidak ada Pabrikan tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    PabrikanSelect.append(option);
                });

                PabrikanSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Pabrikan:', xhr);
                PabrikanSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Distributor Select
        DistributorSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getDistributor") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                DistributorSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Distributor --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    DistributorSelect.append(
                        '<option value="">Tidak ada Distributor tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    DistributorSelect.append(option);
                });

                DistributorSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Distributor:', xhr);
                DistributorSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Rak Select
        RakSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getRak") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {

                RakSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Rak --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    RakSelect.append(
                        '<option value="">Tidak ada Rak tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    RakSelect.append(option);
                });

                RakSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Rak:', xhr);
                RakSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // --- Satuan Select
        SatuanSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("masterObat.getSatuan") }}",
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                SatuanSelect.prop('disabled', false).empty().append(
                    '<option value="">-- Pilih Satuan --</option>');

                let data = Array.isArray(response) ? response : (response.data || []);

                if (data.length === 0) {
                    SatuanSelect.append('<option value="">Tidak ada Satuan tersedia</option>');
                    return;
                }

                data.forEach(item => {
                    let nama = item.nama || item.name || 'Tanpa Nama';
                    let option = new Option(nama, item.id, false, false);
                    SatuanSelect.append(option);
                });

                SatuanSelect.trigger('change'); // update tampilan Select2
            },
            error: function(xhr) {
                console.error('Error Satuan:', xhr);
                SatuanSelect.prop('disabled', false)
                    .empty()
                    .append('<option value="">Gagal memuat data kategori</option>');
            }
        });

        // === Tabel Data Master Obat ===
        // --- DataTable
        let masterObatTable = $('#tableObat').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            scrollX: true, // aktifkan scroll horizontal
            ajax: {
                url: "{{ route("masterObat.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'actions',
                    name: 'actions',
                    orderable: false,
                    searchable: false,
                    className: 'sticky-action text-center'
                },
                {
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'sticky-no text-center'
                },
                {
                    data: 'kode_obat',
                    name: 'kode_obat'
                },
                {
                    data: 'nama_obat',
                    name: 'nama_obat'
                },
                {
                    data: 'category_id',
                    name: 'category_id'
                },
                {
                    data: 'main_category_id',
                    name: 'main_category_id'
                },
                {
                    data: 'sub_kategori_id',
                    name: 'sub_kategori_id'
                },
                {
                    data: 'golongan_id',
                    name: 'golongan_id'
                },
                {
                    data: 'satuan_id',
                    name: 'satuan_id'
                },
                {
                    data: 'sediaan_id',
                    name: 'sediaan_id'
                },
                {
                    data: 'pabrikan_id',
                    name: 'pabrikan_id'
                },
                {
                    data: 'distributor_id',
                    name: 'distributor_id'
                },
                {
                    data: 'rak_id',
                    name: 'rak_id'
                },
                {
                    data: 'kemasan',
                    name: 'kemasan'
                },
                {
                    data: 'stok_minimum',
                    name: 'stok_minimum'
                },
                {
                    data: 'stok',
                    name: 'stok'
                },
                {
                    data: 'harga_beli',
                    name: 'harga_beli',
                    render: function(data) {
                        if (!data) return '-';
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'harga_jual',
                    name: 'harga_jual',
                    render: function(data) {
                        if (!data) return '-';
                        return 'Rp ' + parseInt(data).toLocaleString('id-ID');
                    }
                },
                {
                    data: 'jenis',
                    name: 'jenis',
                    render: function(data, type, row) {
                        if (!data) return '-';
                        let badgeClass = data.toLowerCase() === 'paten' ? 'bg-warning' :
                            'bg-primary';
                        return `<span class="badge ${badgeClass}">${data}</span>`;
                    }
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    render: function(data, type, row) {
                        let checked = data == 1 ? 'checked' : '';
                        return `
                    <div class="form-check form-switch mb-0">
                        <input type="checkbox" class="form-check-input toggle-status" 
                            data-id="${row.id}" ${checked} id="switch${row.id}">
                        <label class="form-check-label" for="switch${row.id}"></label>
                    </div>
                `;
                    },
                    orderable: false,
                    searchable: false
                }
            ]
        });

        // --- Auto-adjust posisi kolom No
        masterObatTable.on('draw.dt', function() {
            const actionWidth = $('#tableObat th.sticky-action').outerWidth();
            const styleId = 'stickyNoStyle';
            let styleTag = document.getElementById(styleId);

            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = styleId;
                document.head.appendChild(styleTag);
            }

            styleTag.textContent = `
                .dataTables_wrapper .dataTable th.sticky-no,
                .dataTables_wrapper .dataTable td.sticky-no {
                    position: sticky;
                    left: ${actionWidth}px;
                    background: #fff;
                    z-index: 4;
                    box-shadow: 2px 0 4px rgba(0,0,0,0.03);
                }
            `;
        });
        // === END tabel Data Master Obat ===

        // --- Hilangkan search default bawaan DataTables
        $('.dataTables_filter').hide();

        // --- Hubungkan search custom dengan DataTables
        $('#searchObat').on('keyup', function() {
            masterObatTable.search(this.value).draw();
        });

        // --- Mengaktifkan dan Menonaktifkan Margin
        $('#tableObat').on('change', '.toggle-status', function() {
            let obatId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            console.log('obat ID: ' + obatId + ', Status: ' + status);

            $.ajax({
                url: "{{ route("masterObat.updateStatus") }}", // pastikan route ini ada
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: obatId
                },
                success: function(response) {
                    console.log('Status updated!');
                    // SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status obat berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengubah obat.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        // --- Pemilihan Sub Kategori Berdasarkan Kategori
        $('#kategori').on('change', function() {
            let kategoriId = $(this).val();
            let subKategoriSelect = $('#subKategori');

            // Reset sub kategori
            subKategoriSelect.empty().append('<option value="">-- Pilih Sub Kategori --</option>');

            if (!kategoriId) return; // jika kategori kosong, hentikan

            // Tampilkan status loading
            subKategoriSelect.prop('disabled', true)
                .append('<option>Memuat data...</option>');

            $.ajax({
                url: "{{ route("masterObat.getSubKategori", ":kategori") }}".replace(
                    ':kategori',
                    kategoriId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Bersihkan dan aktifkan dropdown
                    subKategoriSelect.prop('disabled', false).empty()
                        .append('<option value="">-- Pilih Sub Kategori --</option>');

                    if (response.length === 0) {
                        subKategoriSelect.append(
                            '<option value="">Tidak ada sub kategori tersedia</option>');
                        return;
                    }

                    // Isi dengan data dari response
                    response.forEach(item => {
                        let nama = item.nama || item.name || 'Tanpa Nama';
                        subKategoriSelect.append(
                            `<option value="${item.id}">${nama}</option>`);
                    });

                    // Refresh jika pakai Select2
                    if (subKategoriSelect.hasClass('select2')) {
                        subKategoriSelect.trigger('change.select2');
                    }
                },
                error: function() {
                    subKategoriSelect.prop('disabled', false)
                        .empty()
                        .append('<option value="">Gagal memuat data sub kategori</option>');
                }
            });
        });

        // --- Pemilihan Kategori Berdasarkan Main Kategori
        $('#kategoriUtama').on('change', function() {
            let kategoriUtamaId = $(this).val();
            let kategoriSelect = $('#kategori');

            // Reset sub kategori
            kategoriSelect.empty().append('<option value="">-- Pilih Sub Kategori --</option>');

            if (!kategoriUtamaId) return; // jika kategori kosong, hentikan

            // Tampilkan status loading
            kategoriSelect.prop('disabled', true)
                .append('<option>Memuat data...</option>');

            $.ajax({
                url: "{{ route("masterObat.getMainKategori", ":kategoriUtama") }}".replace(
                    ':kategoriUtama',
                    kategoriUtamaId),
                type: 'GET',
                dataType: 'json',
                success: function(response) {
                    // Bersihkan dan aktifkan dropdown
                    kategoriSelect.prop('disabled', false).empty()
                        .append('<option value="">-- Pilih Sub Kategori --</option>');

                    if (response.length === 0) {
                        kategoriSelect.append(
                            '<option value="">Tidak ada sub kategori tersedia</option>');
                        return;
                    }

                    // Isi dengan data dari response
                    response.forEach(item => {
                        let nama = item.nama || item.name || 'Tanpa Nama';
                        kategoriSelect.append(
                            `<option value="${item.id}">${nama}</option>`);
                    });

                    // Refresh jika pakai Select2
                    if (kategoriSelect.hasClass('select2')) {
                        kategoriSelect.trigger('change.select2');
                    }
                },
                error: function() {
                    kategoriSelect.prop('disabled', false)
                        .empty()
                        .append('<option value="">Gagal memuat data sub kategori</option>');
                }
            });
        });

        // --- Submit form
        $('#obatForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let obatId = $('#obatId').val();

            let url = obatId ?
                "{{ route("masterObat.update", ":id") }}".replace(':id', obatId) :
                "{{ route("masterObat.store") }}";

            let method = obatId ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#obatModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#obatForm')[0].reset();
                        $('#obatId').val('');
                        masterObatTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#obatForm').find('.invalid-feedback').text('');
                        $('#obatForm').find('.form-control').removeClass(
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

        // --- Mengaktifkan dan Menonaktifkan Status
        $('#tableObat').on('change', '.toggle-status', function() {
            let obatId = $(this).data('id');
            let status = $(this).is(':checked') ? 1 : 0;
            console.log('obat ID: ' + obatId + ', Status: ' + status);

            $.ajax({
                url: "{{ route("masterObat.updateStatus") }}", // pastikan route ini ada
                method: 'PUT',
                data: {
                    _token: '{{ csrf_token() }}',
                    status: status,
                    id: obatId
                },
                success: function(response) {
                    // SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil!',
                        text: 'Status obat berhasil diperbarui.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                },
                error: function(err) {
                    console.log(err);
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: 'Terjadi kesalahan saat mengubah obat.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                }
            });
        });

        // === EDIT DATA MASTER OBAT ===
        // === Helper: tunggu option tersedia sebelum set value
        function setSelect2Value(selector, value) {
            const $el = $(selector);
            if (!value) return;

            const maxAttempts = 10;
            let attempts = 0;

            const interval = setInterval(() => {
                const optionExists = $el.find(`option[value="${value}"]`).length > 0;
                if (optionExists || attempts >= maxAttempts) {
                    $el.val(value).trigger('change');
                    clearInterval(interval);
                }
                attempts++;
            }, 200);
        }

        // --- Fungsi Edit
        window.editMasterObat = function(id) {
            isEditMode = true;
            $.ajax({
                url: "{{ route("masterObat.edit", ":id") }}".replace(':id', id),
                type: "GET",
                dataType: "json",
                success: function(response) {
                    console.log(response);

                    // --- Tampilkan modal
                    $('#obatModal').modal('show');
                    $('#obatModalLabel').text('EDIT DATA MASTER OBAT');
                    $('#submitForm').text('Update');

                    // --- Hidden ID
                    $('#obatId').val(response.id);

                    // === ISI INPUT FIELD ===
                    $('[name="kode_obat"]').val(response.kode_obat);
                    $('[name="nama_obat"]').val(response.nama_obat);
                    $('[name="komposisi"]').val(response.komposisi);
                    $('[name="indikasi"]').val(response.indikasi);
                    $('[name="dosis"]').val(response.dosis);
                    $('[name="kemasan"]').val(response.kemasan);
                    $('[name="stok_minimum"]').val(response.stok_minimum);
                    $('[name="harga_beli"]').val(response.harga_beli);
                    $('[name="harga_jual"]').val(response.harga_jual);
                    $('[name="is_generik"]').val(response.is_generik);
                    $('[name="is_active"]').val(response.is_active);

                    // === ISI SELECT2 NON DEPENDENT ===
                    setSelect2Value('[name="sediaan_id"]', response.sediaan_id);
                    setSelect2Value('[name="golongan_id"]', response.golongan_id);
                    setSelect2Value('[name="satuan_id"]', response.satuan_id);
                    setSelect2Value('[name="pabrikan_id"]', response.pabrikan_id);
                    setSelect2Value('[name="distributor_id"]', response.distributor_id);
                    setSelect2Value('[name="rak_id"]', response.rak_id);

                    // === ISI DEPENDENT DROPDOWN SECARA BERTAHAP ===
                    // 1️⃣ Pilih kategori utama dulu
                    setSelect2Value('[name="category_id"]', response.category_id);

                    // 2️⃣ Setelah kategori utama berubah dan main kategori berhasil dimuat lewat AJAX
                    $('select[name="category_id"]').one('change', function() {
                        // Tunggu main kategori selesai dimuat (AJAX selesai)
                        let checkMainKategori = setInterval(() => {
                            let mainOptions = $(
                                    'select[name="main_category_id"] option')
                                .length;
                            if (mainOptions > 1) { // artinya data sudah ada
                                setSelect2Value('[name="main_category_id"]',
                                    response.main_category_id);
                                clearInterval(checkMainKategori);

                                // 3️⃣ Setelah main category terpilih, isi sub kategori
                                $('select[name="main_category_id"]').one(
                                    'change',
                                    function() {
                                        let checkSubKategori = setInterval(
                                            () => {
                                                let subOptions = $(
                                                    '#subKategori option'
                                                ).length;
                                                if (subOptions > 1) {
                                                    setSelect2Value(
                                                        '#subKategori',
                                                        response
                                                        .sub_kategori_id
                                                    );
                                                    clearInterval(
                                                        checkSubKategori
                                                    );
                                                }
                                            }, 200);
                                    });
                            }
                        }, 200);
                    });
                },
                error: function(xhr) {
                    console.error('Gagal mengambil data obat:', xhr);
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Gagal mengambil data obat!',
                    });
                }
            });
        };
        // === END EDIT DATA MASTER OBAT ===

        // --- Hapus Data Obat
        window.deleteMasterObat = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'Master Obat ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("masterObat.destroy", ":id") }}".replace(
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
                                masterObatTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus Master Obat.',
                                'error'
                            );
                        }
                    });
                }
            });
        }

        // --- Download Template
        $('#downloadTemplateBtn').on('click', function() {
            window.location.href = "{{ route("masterObat.exportTemplate") }}";
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
                            title: 'Berhasil',
                            html: `
                                <p>${response.added} data berhasil ditambahkan.</p>
                                <p>${response.skipped} data dilewati (sudah ada).</p>
                            `,
                            timer: 2500,
                            showConfirmButton: false,
                            willClose: () => {
                                // Tutup modal
                                $('#obatModalExcell').modal('hide');

                                // Reload DataTable jika sudah diinisialisasi
                                if (typeof masterObatTable !== 'undefined') {
                                    masterObatTable.ajax.reload(null,
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
