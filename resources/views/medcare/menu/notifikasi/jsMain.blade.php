<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<script>
    $(document).ready(function() {

        // =================== DATATABLE ===================
        window.NotifikasiTable = $('#tableNotifikasi').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            autoWidth: false,
            ajax: {
                url: "{{ route("notifikasi.table") }}",
                type: "GET"
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false
                },
                {
                    data: 'title'
                },
                {
                    data: 'message'
                },
                {
                    data: 'no_po'
                },
                {
                    data: 'waktu'
                },
                {
                    data: 'status',
                    className: 'text-center'
                },
                {
                    data: 'actions',
                    orderable: false,
                    searchable: false
                }
            ],
            rowCallback: function(row, data) {
                if (data.is_unread) {
                    $(row).addClass('notif-unread');
                }
            }
        });

        $('.dataTables_filter').hide();

        $('#searchNotifikasi').on('keyup', function() {
            window.NotifikasiTable.search(this.value).draw();
        });

        // =================== READ NOTIFIKASI ===================
        window.readNotifikasi = function(id, notif) {

            console.log(notif);

            $.ajax({
                url: "{{ route("notifikasi.readNotifikasi") }}",
                type: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    id: id
                },
                success: function() {

                    // 🔔 UPDATE BADGE REALTIME
                    const badge = document.getElementById('notif-count');
                    if (badge) {
                        let count = parseInt(badge.innerText || '0');

                        if (count > 1) {
                            badge.innerText = count - 1;
                        } else {
                            badge.remove(); // hilangkan badge kalau 0
                        }
                    }

                    // Hilangkan highlight unread di tabel
                    $(`button[onclick*="${id}"]`)
                        .closest('tr')
                        .removeClass('notif-unread');

                    /* =====================
                     * 🪟 RENDER MODAL
                     * ===================== */
                    renderNotifModal(notif);

                    // // Redirect ke detail PO
                    // if (url && url !== '#') {
                    //     window.location.href = url;
                    // }
                }
            });
        };

        function renderNotifModal(notif) {

            $('#notif-modal-title').text(notif.title ?? 'Notifikasi');
            $('#notif-modal-body').html('');
            $('#notif-modal-footer').html('');

            switch (notif.type) {

                case 'po_created':
                    renderNotifPO(notif);
                    break;

                default:
                    renderGenericNotif(notif);
            }

            $('#modalReadNotif').modal('show');
        }

        function renderNotifPO(notif) {

            $('#notif-modal-title').html(`
                <i class="bi bi-file-earmark-text me-2"></i> Detail Purchase Order
            `);

            $('#notif-modal-body').html(`
                <div class="text-center text-muted py-4">
                    <div class="spinner-border text-primary"></div>
                    <p class="mt-2">Memuat detail PO...</p>
                </div>
            `);

            $.get(`/medcare/menu/pembelian-dan-penerimaan/pembelian/${notif.po_id}/json`)
                .done(res => {

                    let body = `
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-body">
                            <h6 class="fw-bold mb-3 text-primary">
                                <i class="bi bi-info-circle me-2"></i>Informasi Purchase Order
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Nomor PO</label>
                                    <input class="form-control form-control-sm" value="${res.no_po}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Distributor</label>
                                    <input class="form-control form-control-sm" value="${res.distributor.nama}" readonly>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold">Tanggal PO</label>
                                    <input class="form-control form-control-sm" value="${res.tanggal_po}" readonly>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold">Catatan</label>
                                    <textarea class="form-control form-control-sm" rows="2" readonly>${res.catatan ?? '-'}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body pb-1">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-capsule-pill me-2"></i>Detail Obat
                            </h6>

                            <div class="table-responsive">
                                <table class="table">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Nama Obat</th>
                                            <th>Qty</th>
                                            <th>Harga</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        ${res.details.map(d => `
                                            <tr>
                                                <td>${d.nama_obat}</td>
                                                <td>${d.qty}</td>
                                                <td>${formatRupiah(d.harga_estimasi)}</td>
                                                <td>${formatRupiah(d.subtotal)}</td>
                                            </tr>
                                        `).join('')}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <div class="fw-bold fs-5 text-success">
                            Total: ${formatRupiah(res.total_estimasi)}
                        </div>
                    </div>
                    `;

                    $('#notif-modal-body').html(body);
                });

            $('#notif-modal-footer').html(`
                <button class="btn btn-success" onclick="approvePO(${notif.po_id})">
                    ACCEPT PO
                </button>
                <button class="btn btn-danger" onclick="rejectPO(${notif.po_id})">
                    REJECT PO
                </button>
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            `);
        }

        function renderGenericNotif(notif) {

            $('#notif-modal-body').html(`
                <div class="alert alert-info mb-0">
                    ${notif.message}
                </div>
            `);

            $('#notif-modal-footer').html(`
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            `);
        }



    });
</script>
