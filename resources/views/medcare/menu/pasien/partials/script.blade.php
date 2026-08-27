<script>
    $(function () {
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const routes = {
            table: @json(route('pasien.table')),
            visits: @json(route('pasien.visits')),
            store: @json(route('pasien.store')),
            show: @json(route('pasien.show', ['patient' => '__ID__'])),
            update: @json(route('pasien.update', ['patient' => '__ID__'])),
            destroy: @json(route('pasien.destroy', ['patient' => '__ID__']))
        };
        const defaultBranchId = @json($selectedBranchId);
        let searchTimer = null;
        let visitChart = null;
        let visitPeriod = 'week';
        let visitRequestId = 0;

        const routeFor = (template, id) => template.replace('__ID__', id);
        const escapeHtml = value => $('<div>').text(value == null ? '' : String(value)).html();
        const initials = name => String(name || 'P').trim().split(/\s+/).slice(0, 2).map(word => word.charAt(0).toUpperCase()).join('');
        const formatNumber = value => Number(value || 0).toLocaleString('id-ID');

        function notify(icon, title, text = '') {
            Swal.fire({ icon, title, text, timer: icon === 'success' ? 1800 : undefined, showConfirmButton: icon !== 'success', toast: icon === 'success', position: icon === 'success' ? 'top-end' : 'center' });
        }

        function clearValidation() {
            $('#patientForm .is-invalid').removeClass('is-invalid');
            $('#patientForm .invalid-feedback').text('');
        }

        function showValidation(errors) {
            clearValidation();
            let $first = null;
            Object.entries(errors || {}).forEach(([field, messages]) => {
                const $field = $('#patientForm [name="' + field + '"]');
                if (!$field.length) return;
                $field.addClass('is-invalid').siblings('.invalid-feedback').text(messages[0] || 'Data tidak valid.');
                $first = $first || $field;
            });
            $first?.trigger('focus');
        }

        const table = $('#patientTable').DataTable({
            processing: true,
            serverSide: true,
            pageLength: 10,
            lengthChange: false,
            order: [[0, 'asc']],
            dom: 'rt<"d-flex flex-wrap align-items-center justify-content-between"ip>',
            ajax: {
                url: routes.table,
                data: data => { data.branch_id = $('#patientBranchFilter').val(); },
                error: () => notify('error', 'Data gagal dimuat', 'Silakan muat ulang halaman.')
            },
            columns: [
                {
                    data: 'name', name: 'name',
                    render: data => `<div class="patient-identity"><span class="patient-avatar">${escapeHtml(initials(data))}</span><div><strong>${escapeHtml(data)}</strong><small>Pasien tersimpan</small></div></div>`
                },
                {
                    data: 'phone', name: 'phone',
                    render: data => `<div class="patient-contact"><strong><i class="mdi mdi-phone-outline me-1"></i>${escapeHtml(data)}</strong><small>Siap dipilih di kasir</small></div>`
                },
                {
                    data: 'branch_name', name: 'branch_id', orderable: false,
                    render: data => `<span class="patient-branch-pill"><i class="mdi mdi-source-branch"></i>${escapeHtml(data)}</span>`
                },
                { data: 'updated_label', name: 'updated_at', render: data => escapeHtml(data || '-') },
                { data: 'actions', name: 'actions', orderable: false, searchable: false }
            ],
            language: {
                processing: 'Memuat data pasien...', emptyTable: 'Belum ada data pasien.', zeroRecords: 'Pasien tidak ditemukan.',
                info: 'Menampilkan _START_–_END_ dari _TOTAL_ pasien', infoEmpty: 'Menampilkan 0 pasien', paginate: { previous: '‹', next: '›' }
            },
            drawCallback: function () {
                const info = this.api().page.info();
                $('#patientTotal').text(info.recordsTotal.toLocaleString('id-ID'));
                $('#patientFiltered').text(info.recordsDisplay.toLocaleString('id-ID'));
            }
        });

        const reloadTable = resetPage => table.ajax.reload(null, Boolean(resetPage));

        function renderVisitChart(trend) {
            const target = document.getElementById('patientVisitChart');
            if (visitChart) visitChart.destroy();
            target.innerHTML = '';

            if (typeof ApexCharts === 'undefined') {
                target.innerHTML = '<div class="patient-visit-empty"><i class="mdi mdi-chart-line"></i><span>Grafik tidak dapat dimuat.</span></div>';
                return;
            }

            visitChart = new ApexCharts(target, {
                chart: { type: 'area', height: 285, toolbar: { show: false }, fontFamily: 'inherit', animations: { speed: 350 } },
                series: [{ name: 'Kunjungan', data: trend.values || [] }],
                colors: ['#2563eb'],
                stroke: { curve: 'smooth', width: 3 },
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: .28, opacityTo: .03, stops: [0, 95, 100] } },
                dataLabels: { enabled: false },
                grid: { borderColor: '#e8eef6', strokeDashArray: 4, padding: { left: 8, right: 8 } },
                xaxis: { categories: trend.labels || [], axisBorder: { show: false }, axisTicks: { show: false }, labels: { style: { colors: '#7b899e', fontSize: '10px' }, hideOverlappingLabels: true } },
                yaxis: { min: 0, forceNiceScale: true, labels: { formatter: value => formatNumber(Math.round(value)), style: { colors: '#7b899e', fontSize: '10px' } } },
                tooltip: { y: { formatter: value => `${formatNumber(value)} kunjungan` } },
                noData: { text: 'Belum ada kunjungan' }
            });
            visitChart.render();
        }

        function renderVisitAnalytics(data) {
            $('#patientVisitTotal').text(formatNumber(data.summary.visits));
            $('#patientVisitUnique').text(formatNumber(data.summary.unique_patients));
            $('#patientVisitRepeat').text(formatNumber(data.summary.repeat_visits));
            $('#patientVisitRange').text(`${data.meta.period_label} · ${data.meta.range_label}`);

            const rows = data.top_patients || [];
            $('#patientTopVisitors').html(rows.length ? rows.map((patient, index) => `
                <div class="patient-top-item">
                    <span class="patient-top-rank">${index + 1}</span>
                    <span class="patient-top-avatar">${escapeHtml(initials(patient.name))}</span>
                    <span class="patient-top-copy"><strong>${escapeHtml(patient.name)}</strong><small>${escapeHtml(patient.phone)} · Terakhir ${escapeHtml(patient.last_visit)}</small></span>
                    <b>${formatNumber(patient.visits)}<small>kali</small></b>
                </div>`).join('') : '<div class="patient-visit-empty"><i class="mdi mdi-account-clock-outline"></i><span>Belum ada kunjungan pada periode ini.</span></div>');
            renderVisitChart(data.trend || { labels: [], values: [] });
        }

        function loadVisitAnalytics() {
            const requestId = ++visitRequestId;
            const params = { period: visitPeriod };
            const branchId = $('#patientBranchFilter').val();
            if (branchId) params.branch_id = branchId;

            $('#patientVisitAnalytics').addClass('is-loading');
            $('#patientVisitError').prop('hidden', true).text('');
            $.get(routes.visits, params)
                .done(response => {
                    if (requestId !== visitRequestId) return;
                    renderVisitAnalytics(response.data);
                })
                .fail(xhr => {
                    if (requestId !== visitRequestId) return;
                    $('#patientVisitError').prop('hidden', false).text(xhr.responseJSON?.message || 'Data kunjungan tidak dapat dimuat.');
                })
                .always(() => {
                    if (requestId === visitRequestId) $('#patientVisitAnalytics').removeClass('is-loading');
                });
        }

        $('#patientSearch').on('input', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => table.search(this.value).draw(), 300);
        });
        $('#patientBranchFilter').on('change', () => { reloadTable(true); loadVisitAnalytics(); });
        $('#resetPatientFilter').on('click', function () {
            $('#patientSearch').val('');
            $('#patientBranchFilter').val(defaultBranchId || '');
            table.search('').draw();
            loadVisitAnalytics();
        });
        $('#patientVisitPeriods').on('click', '[data-visit-period]', function () {
            visitPeriod = $(this).data('visit-period');
            $('#patientVisitPeriods [data-visit-period]').removeClass('is-active');
            $(this).addClass('is-active');
            loadVisitAnalytics();
        });

        function resetForm() {
            $('#patientForm')[0].reset();
            $('#patientId').val('');
            $('#patientBranchId').val(defaultBranchId || '');
            $('#patientModalLabel').text('Tambah Pasien');
            $('#patientModalSubtitle').text('Isi nama dan nomor telepon pasien.');
            $('#savePatientButton').html('<i class="mdi mdi-content-save-outline me-1"></i>Simpan Pasien');
            clearValidation();
        }

        function openCreate() {
            resetForm();
            $('#patientModal').modal('show');
            setTimeout(() => $('#patientName').trigger('focus'), 250);
        }

        $('#addPatientButton, #addPatientToolbar').on('click', openCreate);
        $('#patientModal').on('hidden.bs.modal', resetForm);
        $('#patientForm input, #patientForm select').on('input change', function () { $(this).removeClass('is-invalid').siblings('.invalid-feedback').text(''); });

        $('#patientForm').on('submit', function (event) {
            event.preventDefault();
            clearValidation();
            const id = $('#patientId').val();
            const $button = $('#savePatientButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span>Menyimpan...');

            $.ajax({
                url: id ? routeFor(routes.update, id) : routes.store,
                method: id ? 'PUT' : 'POST',
                data: $(this).serialize(),
                success: response => { $('#patientModal').modal('hide'); reloadTable(false); notify('success', response.message); },
                error: function (xhr) {
                    if (xhr.status === 422) { showValidation(xhr.responseJSON.errors); notify('error', 'Periksa kembali data'); return; }
                    notify('error', 'Gagal menyimpan pasien', xhr.responseJSON?.message || 'Terjadi kesalahan pada server.');
                },
                complete: function () {
                    $button.prop('disabled', false).html(id ? '<i class="mdi mdi-content-save-edit-outline me-1"></i>Simpan Perubahan' : '<i class="mdi mdi-content-save-outline me-1"></i>Simpan Pasien');
                }
            });
        });

        $('#patientTable').on('click', '[data-patient-action]', function () {
            const id = $(this).data('id');
            const action = $(this).data('patient-action');

            if (action === 'edit') {
                $.get(routeFor(routes.show, id)).done(function (response) {
                    const patient = response.data;
                    resetForm();
                    $('#patientId').val(patient.id);
                    $('#patientBranchId').val(patient.branch_id);
                    $('#patientName').val(patient.name);
                    $('#patientPhone').val(patient.phone);
                    $('#patientModalLabel').text('Edit Data Pasien');
                    $('#patientModalSubtitle').text('Perbarui nama atau nomor telepon pasien.');
                    $('#savePatientButton').html('<i class="mdi mdi-content-save-edit-outline me-1"></i>Simpan Perubahan');
                    $('#patientModal').modal('show');
                }).fail(() => notify('error', 'Data pasien tidak dapat dibuka'));
                return;
            }

            if (action !== 'delete') return;
            Swal.fire({ icon: 'warning', title: 'Hapus data pasien?', text: 'Riwayat transaksi lama tetap tersimpan.', showCancelButton: true, confirmButtonText: 'Ya, hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc3545' }).then(result => {
                if (!result.isConfirmed) return;
                $.ajax({ url: routeFor(routes.destroy, id), method: 'DELETE' })
                    .done(response => { reloadTable(false); notify('success', response.message); })
                    .fail(xhr => notify('error', 'Gagal menghapus pasien', xhr.responseJSON?.message || 'Terjadi kesalahan pada server.'));
            });
        });

        loadVisitAnalytics();
    });
</script>
