<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {

        // ==== Inisiasi Variable Global dan Function ====
        // --- Variabel global
        let editMode = false;

        // --- Variabel select2
        let DistributorSelect = $('select[name="distributor_id"]');

        // --- Inisialisasi flatpickr   
        flatpickr("#flatpickr-date", {
            dateFormat: "d-m-Y"
        });

        // --- Inisialisasi Select2
        $('.js-example-basic-single').select2({
            placeholder: "-- Pilih --",
            allowClear: false,
            width: 'resolve',
            dropdownParent: $('#pembelianModal .modal-body')
        });

        // --- Setup CSRF untuk semua AJAX request
        $.ajaxSetup({
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content")
            }
        });
        // ==== End Inisiasi Variable Global dan Function ====

        // =================== Inisiasi Modal ===================
        // --- Reset modal ketika dibuka
        $('#pembelianModal').on('show.bs.modal', function() {
            let form = $('#pembelianForm');
            $('#pembelianModalLabel').text('Form Purchase Order');
            form.trigger('reset');

            $('#submitForm').text('Simpan Purchase Order');
            $('#total_estimasi').text('0');
            $('#total_estimasi_input').val(0);
            $('#penerimaan_id').val('');

            // Reset error state
            form.find('.invalid-feedback').text('');
            form.find('.form-control, .form-select').removeClass('is-invalid');

            // Reset detail-wrapper jadi hanya 1 baris kosong
            $('#detail-wrapper').html(`
                <div class="row g-3 mb-3 detail-item align-items-end border-bottom pb-3">
                    <div class="col-md-4">
                        <label class="form-label">Obat</label>
                        <select class="js-example-basic-single form-select" data-width="100%" name="obat_id[]" required>
                        
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <select class="form-select satuan-select" name="satuan_id[]" disabled required>
                            <option value="">-- Pilih Satuan --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" name="qty[]" min="1" value="1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Harga Estimasi</label>
                        <input type="number" class="form-control harga_estimasi" name="harga_estimasi[]" min="0" step="0.01" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Subtotal</label>
                        <input type="number" class="form-control subtotal" name="subtotal[]" readonly>
                    </div>

                    <div class="col-md-12 mt-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `);

            // Generate No PO hanya kalau TAMBAH
            $(this).one('shown.bs.modal', function() {
                if (!editMode) {
                    $.get('{{ route("pembelian.generateNoPO") }}', function(res) {
                        form.find('input[name="no_po"]').val(res);
                    });
                }
            });

            let $obatSelects = $('#detail-wrapper').find('select[name="obat_id[]"]');
            loadObatInto($obatSelects);
        });

        // --- Reset modal ketika ditutup
        $('#pembelianModal').on('hide.bs.modal', function() {
            editMode = false;
            $('#pembelian_id').val('');

        })
        // =================== End Inisiasi Modal ===============

        // =================== Inisiasi Event Handler ===================
        // --- Tambah baris detail obat baru
        $(document).on('click', '#addDetail', function() {
            let newDetail = `
                <div class="row g-3 mb-3 detail-item align-items-end border-bottom pb-3">
                    <div class="col-md-4">
                        <label class="form-label">Obat</label>
                        <select class="js-example-basic-single form-select" data-width="100%" name="obat_id[]" required>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Satuan</label>
                        <select class="form-select satuan-select" name="satuan_id[]" disabled required>
                            <option value="">-- Pilih Satuan --</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="form-label">Qty</label>
                        <input type="number" class="form-control" name="qty[]" min="1" value="1" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Harga Estimasi</label>
                        <input type="number" class="form-control harga_estimasi" name="harga_estimasi[]" min="0" step="0.01" value="0">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label">Subtotal</label>
                        <input type="number" class="form-control subtotal" name="subtotal[]" readonly>
                    </div>

                    <div class="col-md-12 mt-2 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-detail">
                            <i class="bi bi-trash"></i> Hapus
                        </button>
                    </div>
                </div>
            `;
            $('#detail-wrapper').append(newDetail);
            // 3. Ambil select obat di BARIS BARU saja
            let $newSelect = $('#detail-wrapper .detail-item').last().find('select[name="obat_id[]"]');

            // 4. Jalankan load obat + Select2 hanya untuk select BARU
            loadObatInto($newSelect);
        });

        // --- Hapus baris detail obat
        $(document).on('click', '.remove-detail', function() {
            if ($('.detail-item').length > 1) {
                $(this).closest('.detail-item').remove();
                hitungTotal();
            }
        });

        // --- Hitung subtotal dan total otomatis
        $(document).on('input', '.harga_estimasi, [name="qty[]"]', function() {
            const row = $(this).closest('.detail-item');
            const qty = parseFloat(row.find('[name="qty[]"]').val()) || 0;
            const harga = parseFloat(row.find('.harga_estimasi').val()) || 0;
            const subtotal = qty * harga;
            row.find('.subtotal').val(subtotal.toFixed(2));
            hitungTotal();
        });

        // --- Fungsi hitung total estimasi keseluruhan
        function hitungTotal() {
            let total = 0;
            $('.subtotal').each(function() {
                total += parseFloat($(this).val()) || 0;
            });
            $('#total_estimasi').text(total.toLocaleString('id-ID'));
            $('#total_estimasi_input').val(total);
        }

        // --- Get data distributor
        DistributorSelect.prop('disabled', true).append('<option>Memuat data...</option>');
        $.ajax({
            url: "{{ route("pembelian.getDistributor") }}",
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

        // --- Get data obat
        function loadObatInto($select, selectedValue = null) {
            if (!$select || $select.length === 0) return;

            $select.each(function() {
                $(this).prop('disabled', true).html('<option>Memuat data...</option>');
            });

            $.ajax({
                url: "{{ route("pembelian.getObat") }}",
                type: 'GET',
                dataType: 'json',
                success: function(response) {

                    let data = Array.isArray(response) ? response : (response.data || []);

                    $select.each(function() {
                        let s = $(this);

                        s.prop('disabled', false)
                            .empty()
                            .append('<option value="">-- Pilih Obat --</option>');

                        if (!data || data.length === 0) {
                            s.append('<option value="">Tidak ada Obat tersedia</option>');
                        } else {
                            data.forEach(item => {
                                let text = (item.nama_obat || 'Tanpa Nama') +
                                    (item.kode_obat ? (' (' + item.kode_obat +
                                        ')') : '');

                                let option = new Option(text, item.id, false,
                                    false);

                                // Simpan harga beli
                                $(option).attr('data-harga', item.harga_beli || 0);

                                s.append(option);
                            });
                        }

                        // destroy select2 sebelumnya
                        if (s.hasClass('select2-hidden-accessible')) {
                            s.select2('destroy');
                        }

                        // init select2
                        s.select2({
                            placeholder: "-- Pilih Obat --",
                            width: 'resolve',
                            dropdownParent: $('#pembelianModal .modal-body')
                        });

                        // ========== FIX PALING PENTING ==========
                        // Set nilai obat yang harus dipilih saat edit
                        if (selectedValue) {
                            s.val(selectedValue).trigger('change');
                        }
                        // =========================================
                    });
                },
                error: function(xhr) {
                    console.error('Error Obat:', xhr);

                    $select.each(function() {
                        let s = $(this);

                        s.prop('disabled', false)
                            .empty()
                            .append('<option value="">Gagal memuat data obat</option>');

                        if (s.hasClass('select2-hidden-accessible')) {
                            s.select2('destroy');
                        }

                        s.select2({
                            placeholder: "-- Pilih Obat --",
                            width: 'resolve',
                            dropdownParent: $('#pembelianModal .modal-body')
                        });
                    });
                }
            });
        }

        // Ketika pengguna memilih obat
        $(document).on('change', 'select[name="obat_id[]"]', function() {

            let row = $(this).closest('.detail-item');
            let obatId = $(this).val();
            let selectedOption = $(this).find('option:selected');

            let $satuanSelect = row.find('.satuan-select');

            // reset
            $satuanSelect
                .html('<option value="">-- Pilih Satuan --</option>')
                .prop('disabled', true);

            row.find('.harga_estimasi').val(0);
            row.find('.subtotal').val(0);

            if (!obatId) {
                hitungTotal();
                return;
            }

            $.ajax({
                url: "{{ route("pembelian.getKonversiSatuan") }}",
                type: 'GET',
                dataType: 'json',
                data: {
                    obat_id: obatId
                },
                success: function(data) {
                    // ===== NORMALISASI (OBJECT / ARRAY) =====
                    let list = Array.isArray(data) ? data : [data];

                    if (!list.length || !list[0]?.satuan) {
                        fallbackHargaDefault();
                        return;
                    }

                    // urutkan konversi terkecil
                    list.sort((a, b) => a.konversi - b.konversi);

                    let options = '<option value="">-- Pilih Satuan --</option>';

                    list.forEach(item => {
                        options += `
                    <option value="${item.id}"
                            data-konversi="${item.konversi}">
                        ${item.satuan.nama}
                    </option>
                `;
                    });

                    $satuanSelect
                        .html(options)
                        .prop('disabled', false);

                    // ================= EDIT MODE =================
                    if (editMode) {
                        let satuanLama = row.data('satuan-terpilih');
                        if (satuanLama) {
                            $satuanSelect.val(satuanLama).trigger('change');
                        }
                    }
                    // ================= TAMBAH MODE =================
                    else {
                        $satuanSelect.prop('selectedIndex', 1).trigger('change');
                    }
                },
                error: function() {
                    fallbackHargaDefault();
                }
            });

            // ===== FALLBACK JIKA TIDAK ADA KONVERSI =====
            function fallbackHargaDefault() {
                let harga = parseFloat(selectedOption.data('harga')) || 0;
                let qty = parseFloat(row.find('[name="qty[]"]').val()) || 1;

                row.find('.harga_estimasi').val(harga);
                row.find('.subtotal').val((harga * qty).toFixed(2));

                hitungTotal();
            }
        });

        // --- Format Rupiah 
        function formatRupiah(angka) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                minimumFractionDigits: 0
            }).format(angka);
        }

        // --- Konversi Satuan
        $(document).on('change', '.satuan-select', function() {

            let row = $(this).closest('.detail-item');
            let selected = $(this).find(':selected');

            let hargaDasar = parseFloat(
                row.find('select[name="obat_id[]"] option:selected').data('harga')
            ) || 0;

            let konversi = parseFloat(selected.data('konversi')) || 1;
            let qty = parseFloat(row.find('[name="qty[]"]').val()) || 1;

            let harga = hargaDasar * konversi;

            row.find('.harga_estimasi').val(harga);
            row.find('.subtotal').val((harga * qty).toFixed(2));

            hitungTotal();
        });

        // =================== End Inisiasi Event Handler ===============

        // =================== Inisiasi DataTable ======================
        // --- DataTable
        let PembelianTable = $('#tablePembelian').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("pembelian.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'approved_by',
                    render: function(data) {
                        if (!data) {
                            return `<span class="badge bg-danger">Belum disetujui</span>`;
                        }
                        return `<span class="badge bg-success">${data}</span>`;
                    }
                },
                {
                    data: 'no_po',
                    name: 'no_po'
                },
                {
                    data: 'tanggal_po',
                    name: 'tanggal_po'
                },
                {
                    data: 'branch_id',
                    render: function(data) {
                        return `<span class="badge bg-primary">${data}</span>`;
                    }
                },
                {
                    data: 'distributor_id',
                    render: function(data) {
                        return `<span class="badge bg-info text-dark">Distributor ${data}</span>`;
                    }
                },
                {
                    data: 'total_estimasi',
                    render: function(data) {
                        return `<span class="badge bg-success">${formatRupiah(data)}</span>`;
                    }
                },
                {
                    data: 'status',
                    render: function(data) {
                        let warna =
                            data === 'approved' ? 'success' :
                            data === 'draft' ? 'warning' :
                            data === 'rejected' ? 'danger' :
                            'secondary';

                        return `<span class="badge bg-${warna} text-uppercase">${data}</span>`;
                    }
                },
                {
                    data: 'catatan',
                    name: 'catatan',
                },
                {
                    data: 'created_by',
                    name: 'created_by',
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
        $('#searchPembelian').on('keyup', function() {
            PembelianTable.search(this.value).draw();
        });
        // =================== End Inisiasi DataTable ==================

        // =================== Inisiasi Action =========================
        // --- Submit form
        $('#pembelianForm').on('submit', function(e) {
            e.preventDefault();

            let formData = $(this).serialize();
            let pembelian_id = $('#pembelian_id').val();

            let url = pembelian_id ?
                "{{ route("pembelian.update", ":id") }}".replace(':id', pembelian_id) :
                "{{ route("pembelian.store") }}";

            let method = pembelian_id ? 'PUT' : 'POST';

            $.ajax({
                url: url,
                method: method,
                data: formData,
                success: function(response) {
                    if (response.status === 'success') {
                        $('#pembelianModal').modal('hide');

                        Swal.fire({
                            icon: 'success',
                            title: response.message,
                            toast: true,
                            position: 'top-end',
                            timer: 3000,
                            timerProgressBar: true,
                            showConfirmButton: false,
                        });

                        $('#pembelianForm')[0].reset();
                        $('#pembelian_id').val('');
                        PembelianTable.ajax.reload();
                    }
                },
                error: function(xhr) {
                    if (xhr.status === 422) {
                        let errors = xhr.responseJSON.errors;
                        let errorMessages = [];

                        // reset semua error dulu
                        $('#pembelianForm').find('.invalid-feedback').text('');
                        $('#pembelianForm').find('.form-control').removeClass(
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

        // --- Edit Pembelian
        window.editPembelian = function(id) {

            editMode = true;

            $.ajax({
                url: "{{ route("pembelian.edit", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(response) {

                    let header = response;
                    let detail = response.details ?? [];

                    $('#pembelianModal').modal('show');
                    $('#pembelianModalLabel').text('Edit Purchase Order');
                    $('#submitForm').text('Update');

                    $('#pembelian_id').val(header.id);
                    $('input[name="no_po"]').val(header.no_po);
                    $('#distributor_id').val(header.distributor_id).trigger('change');

                    $('input[name="tanggal"]').val(
                        moment(header.tanggal_po).format('DD-MM-YYYY')
                    );

                    $('textarea[name="catatan"]').val(header.catatan ?? '');

                    $('#detail-wrapper').empty();

                    detail.forEach(item => {

                        let row = `
                            <div class="row g-3 mb-3 detail-item align-items-end border-bottom pb-3"
                                data-satuan-terpilih="${item.satuan_konversi.id}">

                                <div class="col-md-4">
                                    <label class="form-label">Obat</label>
                                    <select class="js-example-basic-single form-select obatSelect"
                                            name="obat_id[]" required>
                                        <option value="${item.obat_id}">Loading...</option>
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Satuan</label>
                                    <select class="form-select satuan-select"
                                            name="satuan_id[]" disabled required>
                                        <option value="">-- Pilih Satuan --</option>
                                    </select>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Qty</label>
                                    <input type="number" class="form-control"
                                        name="qty[]" value="${item.qty}" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Harga Estimasi</label>
                                    <input type="number" class="form-control harga_estimasi"
                                        name="harga_estimasi[]" value="${item.harga_estimasi}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Subtotal</label>
                                    <input type="number" class="form-control subtotal"
                                        name="subtotal[]" value="${item.subtotal}" readonly>
                                </div>

                                <div class="col-md-12 mt-2 d-flex justify-content-end">
                                    <button type="button"
                                            class="btn btn-outline-danger btn-sm remove-detail">
                                        <i class="bi bi-trash"></i> Hapus
                                    </button>
                                </div>
                            </div>
                        `;

                        $('#detail-wrapper').append(row);
                    });


                    // load obat + trigger change otomatis
                    $('.obatSelect').each(function(i) {
                        loadObatInto($(this), detail[i].obat_id);
                    });

                    hitungTotal();
                }
            });
        };

        // --- Detail
        window.lihatPembelian = function(id) {
            $.ajax({
                url: "{{ route("pembelian.show", ":id") }}".replace(':id', id),
                type: "GET",
                success: function(res) {
                    console.log(res);

                    let po = res.original; // <--- KUNCI PENTING
                    let detail = po.details;

                    // Header
                    $('#detail_no_po').val(po.no_po);
                    $('#detail_distributor').val(po.distributor_name ?? 'Tidak diketahui');
                    $('#detail_tanggal_po').val(moment(po.tanggal_po).format('DD-MM-YYYY'));
                    $('#detail_catatan').val(po.catatan ?? '-');

                    // Kosongkan tabel
                    $('#detailObatTable tbody').empty();
                    // Tampilkan jumlah item
                    $('#detail_total_item').text(detail.length);


                    // Detail obat
                    detail.forEach(item => {
                        $('#detailObatTable tbody').append(`
                    <tr>
                        <td>${item.nama_obat}</td>
                        <td>${item.satuan_konversi.satuan.nama}</td>
                        <td>${item.qty}</td>
                        <td>Rp ${Number(item.harga_estimasi).toLocaleString('id-ID')}</td>
                        <td>Rp ${Number(item.subtotal).toLocaleString('id-ID')}</td>
                    </tr>
                `);
                    });

                    // Total Estimasi
                    $('#detail_total_estimasi').text(
                        "Rp " + Number(po.total_estimasi).toLocaleString("id-ID")
                    );

                    // Tampilkan modal
                    $('#pembelianModalDetail').modal('show');
                }
            });
        }

        // --- Hapus
        window.deletePembelian = function(id) {
            // Tampilkan konfirmasi hapus
            Swal.fire({
                title: 'Apakah Anda yakin?',
                text: 'PO ini akan dihapus secara permanen!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Kirim request DELETE menggunakan AJAX
                    $.ajax({
                        url: "{{ route("pembelian.destroy", ":id") }}".replace(
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
                                PembelianTable.ajax.reload(); // Reload DataTables
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
                                'Terjadi kesalahan saat menghapus PO.',
                                'error'
                            );
                        }
                    });
                }
            });
        }
        // ==================== End Inisiasi Action ====================

    });
</script>
