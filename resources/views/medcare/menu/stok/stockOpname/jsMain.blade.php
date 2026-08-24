<script>
    $(function() {
        const routes = {
            table: @json(route('stockOpname.table')),
            store: @json(route('stockOpname.store')),
            show: @json(route('stockOpname.show', ':id')),
            update: @json(route('stockOpname.update', ':id')),
            destroy: @json(route('stockOpname.destroy', ':id')),
            start: @json(route('stockOpname.start', ':id')),
            counts: @json(route('stockOpname.counts', ':id')),
            submit: @json(route('stockOpname.submit', ':id')),
            reasons: @json(route('stockOpname.reasons', ':id')),
            verify: @json(route('stockOpname.verify', ':id')),
            approve: @json(route('stockOpname.approve', ':id')),
            reject: @json(route('stockOpname.reject', ':id')),
            adjust: @json(route('stockOpname.adjust', ':id'))
        };
        const statusOrder = ['draft', 'counting', 'awaiting_verification', 'awaiting_approval', 'approved', 'adjusted'];
        const statusIcons = {
            draft: 'mdi-file-document-edit-outline', counting: 'mdi-counter', awaiting_verification: 'mdi-shield-search-outline',
            awaiting_approval: 'mdi-account-check-outline', approved: 'mdi-check-decagram-outline', adjusted: 'mdi-database-check-outline'
        };
        const actionLabels = {
            create: 'Draft dibuat', update: 'Draft diperbarui', start_counting: 'Penghitungan dimulai', save_counts: 'Hasil hitung disimpan',
            submit_verification: 'Stok fisik disubmit', save_reasons: 'Alasan selisih disimpan', verify: 'Divalidasi & direkonsiliasi', approve: 'Disetujui', reject: 'Dikembalikan', post_adjustment: 'Penyesuaian diposting'
        };
        const detailStatusMeta = {
            draft: {
                eyebrow: 'TAHAP 01 · PERENCANAAN', title: 'Draft siap ditinjau', icon: 'mdi-file-document-edit-outline',
                description: 'Pastikan area, tanggal, dan instruksi sudah tepat sebelum sesi diotorisasi.', workflow: 'Menunggu otorisasi sesi'
            },
            counting: {
                eyebrow: 'TAHAP 02 · BLIND COUNT', title: 'Penghitungan fisik berlangsung', icon: 'mdi-counter',
                description: 'Kuantitas sistem dilindungi agar hasil hitung tetap objektif dan dapat diaudit.', workflow: 'Sesi aktif & operasional terkunci'
            },
            awaiting_verification: {
                eyebrow: 'TAHAP 03 · VERIFIKASI', title: 'Selisih perlu direkonsiliasi', icon: 'mdi-shield-search-outline',
                description: 'Tinjau stok fisik, transaksi setelah submit, dan alasan setiap selisih.', workflow: 'Menunggu validasi petugas'
            },
            awaiting_approval: {
                eyebrow: 'TAHAP 04 · PERSETUJUAN', title: 'Dokumen menunggu persetujuan', icon: 'mdi-account-check-outline',
                description: 'Hasil validasi siap diperiksa sebelum perubahan stok diizinkan.', workflow: 'Menunggu keputusan penyetuju'
            },
            approved: {
                eyebrow: 'TAHAP 05 · SIAP POSTING', title: 'Penyesuaian telah disetujui', icon: 'mdi-check-decagram-outline',
                description: 'Dokumen sudah lolos kontrol dan siap diposting ke stok serta kartu stok.', workflow: 'Siap posting penyesuaian'
            },
            adjusted: {
                eyebrow: 'TAHAP 06 · SELESAI', title: 'Control document telah ditutup', icon: 'mdi-database-check-outline',
                description: 'Penyesuaian final sudah diterapkan dan seluruh aktivitas tersimpan di audit trail.', workflow: 'Selesai & terdokumentasi'
            }
        };
        let currentOpname = null;

        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        function url(template, id) { return template.replace(':id', id); }
        function esc(value) { return $('<div>').text(value == null ? '' : value).html(); }
        function number(value) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 2 }).format(Number(value || 0)); }
        function date(value) { if (!value) return '-'; const parts = String(value).slice(0, 10).split('-'); return parts.length === 3 ? `${parts[2]}/${parts[1]}/${parts[0]}` : value; }
        function dateTime(value) { if (!value) return '-'; return `${date(value)} ${String(value).slice(11, 16)}`; }
        function statusBadge(row) { return `<span class="opname-status-badge is-${esc(row.status)}"><i class="mdi ${statusIcons[row.status] || 'mdi-circle-outline'}"></i>${esc(row.status_label)}</span>`; }
        function modeBadge(row) { const locked = row.status === 'counting'; return `<span class="opname-mode-badge ${locked ? 'is-freeze' : 'is-record'}"><i class="mdi ${locked ? 'mdi-lock-outline' : 'mdi-lock-open-check-outline'}"></i>${esc(row.transaction_mode_label)}</span>`; }
        function ajaxError(xhr, fallback) {
            const json = xhr.responseJSON || {};
            const errors = json.errors || {};
            const first = Object.values(errors).flat()[0];
            Swal.fire('Belum dapat diproses', first || json.message || fallback, 'error');
        }
        function button(icon, title, css, click) { return `<button type="button" class="btn btn-sm ${css}" title="${title}" aria-label="${title}" onclick="${click}"><i class="mdi ${icon}"></i></button>`; }

        const table = $('#stockOpnameTable').DataTable({
            processing: true,
            serverSide: false,
            ajax: {
                url: routes.table,
                data: function(data) { data.status = $('#opnameStatusFilter').val(); data.branch_id = $('#opnameBranchFilter').val(); }
            },
            order: [],
            pageLength: 10,
            lengthChange: false,
            dom: 'rt<"d-flex align-items-center justify-content-between flex-wrap gap-2 p-3"ip>',
            columns: [
                { data: 'DT_RowIndex', searchable: false, orderable: false, render: value => `<span class="opname-row-number">${number(value)}</span>` },
                { data: null, render: row => `<div class="opname-doc"><span class="opname-doc-icon"><i class="mdi mdi-file-document-check-outline"></i></span><div><strong>${esc(row.nomor)}</strong><small>${date(row.tanggal_opname)} &middot; ${esc(row.created_by)}</small></div></div>` },
                { data: null, render: row => `<div class="opname-location"><strong>${esc(row.branch)}</strong><small><i class="mdi mdi-map-marker-outline"></i> ${esc(row.lokasi)}</small></div>` },
                { data: null, render: row => modeBadge(row) },
                { data: null, render: row => { const total = Number(row.details_count || 0); const counted = Number(row.counted_count || 0); const pct = total ? Math.round(counted / total * 100) : 0; return `<div class="opname-progress"><div class="opname-progress-head"><span>Terisi</span><strong>${pct}%</strong></div><div class="opname-progress-line"><span style="width:${pct}%"></span></div><small>${number(counted)} dari ${number(total)} item</small></div>`; } },
                { data: null, render: row => statusBadge(row) },
                { data: null, searchable: false, orderable: false, render: row => {
                    let html = button('mdi-eye-outline', 'Lihat detail', 'btn-outline-primary', `viewStockOpname(${row.id})`);
                    if (row.report_url) html += `<a class="btn btn-sm btn-outline-danger" href="${esc(row.report_url)}" title="Unduh laporan lengkap PDF" aria-label="Unduh laporan lengkap PDF"><i class="mdi mdi-file-pdf-box"></i></a>`;
                    if (row.status === 'draft' && row.can_manage) {
                        html += button('mdi-pencil-outline', 'Edit draft', 'btn-outline-secondary', `editStockOpname(${row.id})`);
                        html += button('mdi-play-outline', 'Otorisasi dan mulai hitung', 'btn-outline-success', `startStockOpname(${row.id})`);
                        html += button('mdi-trash-can-outline', 'Hapus draft', 'btn-outline-danger', `deleteStockOpname(${row.id})`);
                    }
                    return `<div class="opname-actions">${html}</div>`;
                } }
            ],
            createdRow: function(row, data) { $(row).addClass(`is-${data.status}`); },
            language: { processing: 'Memuat stock opname...', emptyTable: 'Belum ada dokumen stock opname.', zeroRecords: 'Stock opname yang dicari tidak ditemukan.', info: 'Menampilkan _START_–_END_ dari _TOTAL_ dokumen', infoEmpty: '0 dokumen', paginate: { previous: '‹', next: '›' } },
            drawCallback: function() { $('#opnameTableInfo').text(`${this.api().rows({ filter: 'applied' }).count()} dokumen ditampilkan`); }
        });

        function renderOperationalSummary(summary) {
            const total = Number(summary.total || 0);
            const draft = Number(summary.draft || 0);
            const counting = Number(summary.counting || 0);
            const waiting = Number(summary.waiting || 0);
            const approved = Number(summary.approved || 0);
            const adjusted = Number(summary.adjusted || 0);
            const actionQueue = waiting + approved;
            const completion = total ? Math.round((adjusted / total) * 100) : 0;
            let title = 'Belum ada dokumen opname';
            let subtitle = 'Buat draft baru untuk memulai kontrol stok fisik.';
            let badge = 'Siap mulai';
            let badgeClass = 'is-info';
            let barColor = '#2563eb';

            if (counting > 0) {
                title = `${number(counting)} sesi penghitungan sedang aktif`;
                subtitle = 'Pantau kelengkapan stok fisik sebelum mengirim dokumen ke verifikasi.';
                badge = 'Penghitungan aktif';
                badgeClass = 'is-info';
                barColor = '#2563eb';
            } else if (actionQueue > 0) {
                title = `${number(actionQueue)} dokumen memerlukan tindak lanjut`;
                subtitle = waiting > 0 ? 'Dokumen menunggu verifikasi atau persetujuan.' : 'Dokumen telah disetujui dan siap diposting.';
                badge = 'Perlu tindakan';
                badgeClass = 'is-warning';
                barColor = '#d97706';
            } else if (draft > 0) {
                title = `${number(draft)} draft siap diotorisasi`;
                subtitle = 'Periksa area dan mode transaksi sebelum membuka sesi penghitungan.';
                badge = 'Persiapan';
                badgeClass = 'is-info';
                barColor = '#64748b';
            } else if (total > 0) {
                title = 'Seluruh dokumen opname terkendali';
                subtitle = `${number(adjusted)} dokumen telah selesai diposting ke kartu stok.`;
                badge = 'Terkendali';
                badgeClass = 'is-safe';
                barColor = '#059669';
            }

            $('#opnameOperationalText').text(title);
            $('#opnameOperationalSubtext').text(subtitle);
            $('#opnameOperationalBar').css({ width: `${completion}%`, background: barColor });
            $('#opnameOperationalBadge').removeClass('is-safe is-info is-warning').addClass(badgeClass).text(badge);
            $('#opnameActiveInsight').text(number(counting));
            $('#opnameReviewInsight').text(number(actionQueue));
            $('#opnameCompleteInsight').text(number(adjusted));
        }

        $('#stockOpnameTable').on('xhr.dt', function(e, settings, json) {
            const summary = json?.summary || {};
            $('#summaryDraft').text(number(summary.draft)); $('#summaryCounting').text(number(summary.counting)); $('#summaryWaiting').text(number(summary.waiting));
            $('#summaryApproved').text(number(summary.approved)); $('#summaryAdjusted').text(number(summary.adjusted));
            renderOperationalSummary(summary);
        });
        $('#opnameSearch').on('input', function() { $(this).closest('.opname-search').toggleClass('has-value', this.value.length > 0); table.search(this.value).draw(); });
        $('#clearOpnameSearch').on('click', function() { $('#opnameSearch').val('').trigger('input').focus(); });
        $('#opnameStatusFilter,#opnameBranchFilter').on('change', function() { table.ajax.reload(); });
        $('#refreshOpnameTable').on('click', function() {
            const refreshButton = $(this).prop('disabled', true).addClass('is-loading');
            table.ajax.reload(() => refreshButton.prop('disabled', false).removeClass('is-loading'), false);
        });

        function resetForm() {
            const now = new Date();
            const localDate = new Date(now.getTime() - (now.getTimezoneOffset() * 60000)).toISOString().slice(0, 10);
            $('#opnameForm')[0].reset(); $('#opnameFormId').val(''); $('#opnameDate').val(localDate);
            $('#opnameFormTitle').text('Buat Stock Opname'); $('#saveOpnameButton').html('<i class="mdi mdi-content-save-outline"></i> Simpan Draft');
            if ($('#opnameBranch option').length === 2) $('#opnameBranch').val($('#opnameBranch option:eq(1)').val());
        }
        $('#createOpnameButton').on('click', function() { resetForm(); $('#opnameFormModal').modal('show'); });
        $('#opnameForm').on('submit', function(e) {
            e.preventDefault();
            const id = $('#opnameFormId').val();
            const payload = {
                tanggal_opname: $('#opnameDate').val(), branch_id: $('#opnameBranch').val(), rak_id: $('#opnameRack').val() || null,
                catatan: $('#opnameNote').val()
            };
            const submit = $('#saveOpnameButton').prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Menyimpan...');
            $.ajax({ url: id ? url(routes.update, id) : routes.store, method: id ? 'PUT' : 'POST', data: payload })
                .done(response => { $('#opnameFormModal').modal('hide'); table.ajax.reload(null, false); Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1600, showConfirmButton: false }); })
                .fail(xhr => ajaxError(xhr, 'Draft stock opname gagal disimpan.'))
                .always(() => submit.prop('disabled', false).html('<i class="mdi mdi-content-save-outline"></i> Simpan Draft'));
        });

        window.editStockOpname = function(id) {
            $.get(url(routes.show, id)).done(response => {
                const row = response.opname; resetForm(); $('#opnameFormId').val(row.id); $('#opnameDate').val(row.tanggal_opname); $('#opnameBranch').val(row.branch_id);
                $('#opnameRack').val(row.rak_id || ''); $('#opnameNote').val(row.catatan || '');
                $('#opnameFormTitle').text(`Edit ${row.nomor}`); $('#saveOpnameButton').html('<i class="mdi mdi-content-save-outline"></i> Perbarui Draft'); $('#opnameFormModal').modal('show');
            }).fail(xhr => ajaxError(xhr, 'Data draft gagal dimuat.'));
        };

        window.deleteStockOpname = function(id) {
            Swal.fire({ title: 'Hapus draft?', text: 'Dokumen draft akan dihapus permanen.', icon: 'warning', showCancelButton: true, confirmButtonText: 'Ya, Hapus', cancelButtonText: 'Batal', confirmButtonColor: '#dc2626', reverseButtons: true })
                .then(result => { if (!result.isConfirmed) return; $.ajax({ url: url(routes.destroy, id), method: 'DELETE' }).done(response => { table.ajax.reload(null, false); Swal.fire('Terhapus', response.message, 'success'); }).fail(xhr => ajaxError(xhr, 'Draft gagal dihapus.')); });
        };

        window.startStockOpname = function(id) {
            Swal.fire({ title: 'Mulai blind count?', html: 'Stok, Kartu Stok, dan Kasir/POS pada cabang ini akan langsung dikunci. User hanya melihat kolom stok fisik.<br><small>Operasional otomatis dibuka setelah hasil fisik disubmit pertama kali.</small>', icon: 'question', showCancelButton: true, confirmButtonText: 'Kunci & Mulai', cancelButtonText: 'Batal', reverseButtons: true })
                .then(result => { if (!result.isConfirmed) return; runAction(id, 'start', {}, true); });
        };

        window.viewStockOpname = function(id) {
            $('#opnameDetailModal').modal('show');
            $('#detailLoading').removeClass('d-none');
            $('#detailContent').addClass('d-none');
            $('#detailActions').empty();
            $('#detailHeaderStatus').attr('class', 'opname-header-status').html('<span class="opname-status-dot"></span> Menyiapkan status');
            $('#countTabButton').tab('show');
            loadDetail(id);
        };

        function loadDetail(id) {
            $.get(url(routes.show, id)).done(response => { currentOpname = response.opname; renderDetail(currentOpname); $('#detailLoading').addClass('d-none'); $('#detailContent').removeClass('d-none'); })
                .fail(xhr => { $('#opnameDetailModal').modal('hide'); ajaxError(xhr, 'Detail stock opname gagal dimuat.'); });
        }

        function setDetailProgress(filled, total) {
            const safeTotal = Math.max(0, Number(total || 0));
            const safeFilled = Math.min(safeTotal, Math.max(0, Number(filled || 0)));
            const percentage = safeTotal ? Math.round((safeFilled / safeTotal) * 100) : 0;
            $('#detailProgressPercent').text(`${percentage}%`);
            $('#detailStageCount').text(`${number(safeFilled)} dari ${number(safeTotal)} item`);
            $('#detailStageProgress').css('width', `${percentage}%`);
            $('#detailProgressRing').css('--progress', `${percentage * 3.6}deg`).attr('aria-label', `Progress penghitungan ${percentage} persen`);
        }

        function renderDetail(row) {
            $('#detailNumber').text(row.nomor); $('#detailSubtitle').text(`${date(row.tanggal_opname)} • ${row.branch} • ${row.lokasi}`);
            const details = row.details || [];
            const movements = row.movements || [];
            const logs = row.logs || [];
            const statusMeta = detailStatusMeta[row.status] || detailStatusMeta.draft;
            const totalItems = Number(row.details_count || details.length || 0);
            const countedItems = Number(row.counted_count || 0);
            const differenceCount = Number(row.difference_count ?? details.filter(detail => {
                const difference = detail.selisih_validasi == null ? detail.selisih : detail.selisih_validasi;
                return Math.abs(Number(difference || 0)) >= .005;
            }).length);

            $('#detailHeaderStatus').attr('class', `opname-header-status is-${row.status}`).html(`<span class="opname-status-dot"></span>${esc(row.status_label)}`);
            $('#detailStageIcon').html(`<i class="mdi ${statusMeta.icon}"></i>`);
            $('#detailStageEyebrow').text(statusMeta.eyebrow);
            $('#detailStageTitle').text(statusMeta.title);
            $('#detailStageDescription').text(statusMeta.description);
            $('#detailWorkflowHint').html(`<i class="mdi ${row.status === 'adjusted' ? 'mdi-check-decagram-outline' : 'mdi-shield-check-outline'}"></i> ${esc(statusMeta.workflow)}`);
            setDetailProgress(countedItems, totalItems);

            $('#detailDocumentFacts').html([
                { label: 'Tanggal opname', value: date(row.tanggal_opname), icon: 'mdi-calendar-check-outline' },
                { label: 'Cabang', value: row.branch || '-', icon: 'mdi-hospital-building' },
                { label: 'Area / rak', value: row.lokasi || '-', icon: 'mdi-map-marker-radius-outline' },
                { label: 'Operasional', value: row.transaction_mode_label || '-', icon: row.status === 'counting' ? 'mdi-lock-outline' : 'mdi-lock-open-check-outline' }
            ].map(item => `<div class="opname-document-fact"><i class="mdi ${item.icon}"></i><div><small>${item.label}</small><strong title="${esc(item.value)}">${esc(item.value)}</strong></div></div>`).join(''));
            $('#detailDocumentNote').toggleClass('d-none', !row.catatan).find('p').text(row.catatan || '');

            $('#detailSummary').html([
                { label: 'Cakupan Item', value: number(totalItems), meta: 'Obat & batch dalam snapshot', icon: 'mdi-package-variant-closed', tone: 'is-blue' },
                { label: 'Sudah Dihitung', value: `${number(countedItems)} item`, meta: totalItems ? `${Math.round(countedItems / totalItems * 100)}% penghitungan selesai` : 'Belum ada item', icon: 'mdi-progress-check', tone: 'is-green' },
                { label: 'Selisih Terdeteksi', value: row.comparison_visible ? number(differenceCount) : 'Terlindungi', meta: row.comparison_visible ? (differenceCount ? 'Item perlu ditinjau' : 'Seluruh item klop') : 'Dibuka setelah blind count', icon: row.comparison_visible ? 'mdi-scale-unbalanced' : 'mdi-eye-lock-outline', tone: row.comparison_visible && differenceCount ? 'is-red' : 'is-amber' },
                { label: 'Transaksi Terekam', value: number(movements.length), meta: 'Mutasi setelah submit fisik', icon: 'mdi-swap-horizontal-bold', tone: 'is-indigo' },
                { label: 'Verifier', value: esc(row.verified_by || 'Belum ditetapkan'), meta: row.verified_at ? dateTime(row.verified_at) : 'Menunggu tahap verifikasi', icon: 'mdi-shield-account-outline', tone: 'is-amber' },
                { label: 'Penyetuju', value: esc(row.approved_by || 'Belum ditetapkan'), meta: row.approved_at ? dateTime(row.approved_at) : 'Menunggu tahap persetujuan', icon: 'mdi-account-check-outline', tone: 'is-green' }
            ].map(item => `<div class="opname-summary-item ${item.tone}"><span class="opname-summary-icon"><i class="mdi ${item.icon}"></i></span><div><small>${item.label}</small><strong>${item.value}</strong><em>${esc(item.meta)}</em></div></div>`).join(''));
            const labels = { draft: 'Draft', counting: 'Blind Count', awaiting_verification: 'Verifikasi', awaiting_approval: 'Persetujuan', approved: 'Disetujui', adjusted: 'Posting' };
            const descriptions = { draft: 'Rencana sesi', counting: 'Hitung fisik', awaiting_verification: 'Review selisih', awaiting_approval: 'Kontrol akhir', approved: 'Izin posting', adjusted: 'Kartu stok' };
            const currentIndex = statusOrder.indexOf(row.status);
            $('#detailFlow').html(statusOrder.map((status, index) => {
                const isDone = index < currentIndex || row.status === 'adjusted';
                const isCurrent = index === currentIndex && row.status !== 'adjusted';
                return `<div class="opname-detail-flow-step ${isDone ? 'is-done' : ''} ${isCurrent ? 'is-current' : ''}"><span class="opname-flow-marker"><i class="mdi ${isDone ? 'mdi-check' : statusIcons[status]}"></i></span><div><small>0${index + 1}</small><strong>${labels[status]}</strong><em>${descriptions[status]}</em></div></div>`;
            }).join(''));
            renderCounts(row); renderSummary(row); renderMovements(movements); renderAudit(logs); renderActions(row);
            $('#movementTabNav').toggleClass('d-none', !row.can_view_movements);
            $('#auditTabNav').toggleClass('d-none', !row.can_review);
            $('#summaryTabNav').toggleClass('d-none', !row.summary);
            if (!row.can_review) $('.opname-tabs [data-bs-target="#countTab"]').tab('show');
            $('#detailCountBadge').text(details.length); $('#detailMovementBadge').text(movements.length); $('#detailAuditBadge').text(logs.length);
        }

        function renderCounts(row) {
            const editable = row.status === 'counting' && row.can_count;
            const comparison = row.comparison_visible;
            const explainable = row.can_explain;
            const countHelp = editable
                ? 'Hitung langsung di rak dan masukkan stok fisik. Nilai sistem sengaja dirahasiakan.'
                : (row.status === 'counting' ? 'Pengisian stok fisik hanya tersedia untuk user cabang terkait.' : 'Stok sistem sudah dibuka. Periksa selisih dan lengkapi alasan sebelum validasi.');
            $('#countHelp').text(countHelp);
            $('.opname-count-table').toggleClass('is-comparison', comparison);
            $('#comparisonStateBadge').toggleClass('is-visible', comparison).toggleClass('is-protected', !comparison).html(comparison
                ? '<i class="mdi mdi-eye-check-outline"></i> Stok sistem sudah dibuka'
                : '<i class="mdi mdi-eye-off-outline"></i> Kuantitas sistem terlindungi');
            $('#countTableHead').html(comparison
                ? `<th>Obat / Rak</th><th>Batch / ED</th><th>Stok Sistem</th><th>Stok Fisik</th><th>Selisih</th>${row.can_review ? '<th>HPP / Nilai</th>' : ''}<th>Rekonsiliasi Transaksi</th><th>Alasan Selisih</th>`
                : '<th>Obat / Rak</th><th>Batch / ED</th><th>Stok Fisik</th><th>Petugas</th>');
            $('#detailMedicineSearch').val('').closest('.opname-detail-search').removeClass('has-value');
            $('.opname-row-filter').removeClass('is-active').attr('aria-pressed', 'false');
            $('.opname-row-filter[data-row-filter="all"]').addClass('is-active').attr('aria-pressed', 'true');
            $('.opname-row-filter[data-row-filter="pending"]').toggleClass('d-none', comparison);
            $('#detailDifferenceFilter').toggleClass('d-none', !comparison);
            $('#detailFillZero').toggleClass('d-none', !editable);
            $('#countTab').removeClass('has-local-changes');
            $('#countTableBody').html(row.details.map(detail => {
                const physical = detail.stok_fisik;
                const physicalCell = editable ? `<div class="physical-input-wrap"><input type="number" min="0" step="0.01" class="form-control count-physical" value="${physical == null ? '' : physical}" data-original="${physical == null ? '' : physical}" placeholder="0" required><span>${esc(detail.satuan || '')}</span></div>` : `<strong class="physical-value">${physical == null ? '-' : number(physical)} <small>${esc(detail.satuan || '')}</small></strong>`;
                const batchLabel = detail.has_batch ? esc(detail.no_batch) : '<span class="opname-batch-empty"><i class="mdi mdi-package-variant-remove"></i> Belum ada stok/batch</span>';
                const expiryLabel = detail.has_batch ? `ED ${date(detail.expired_date)}` : 'Belum tercatat di cabang';
                const counted = physical != null;
                const finalDifference = detail.selisih_validasi == null ? Number(detail.selisih || 0) : Number(detail.selisih_validasi);
                const differenceClass = finalDifference > 0.005 ? 'is-plus' : (finalDifference < -0.005 ? 'is-minus' : 'is-match');
                const differenceLabel = finalDifference > 0.005 ? `+${number(finalDifference)}` : number(finalDifference);
                const reasonCell = Math.abs(Number(detail.selisih || 0)) < 0.005
                    ? '<span class="opname-match"><i class="mdi mdi-check-circle-outline"></i> Klop</span>'
                    : (explainable
                        ? `<textarea class="form-control reason-input" rows="2" maxlength="1000" data-original="${esc(detail.alasan_selisih || '')}" placeholder="Wajib isi alasan selisih...">${esc(detail.alasan_selisih || '')}</textarea>`
                        : `<span class="difference-reason">${esc(detail.alasan_selisih || 'Belum diisi')}</span>`);
                const reconcileCell = detail.stok_sistem_validasi == null
                    ? '<span class="text-muted">Dihitung saat validasi</span>'
                    : `<div class="reconcile-cell"><strong>Target ${number(detail.stok_target_validasi)}</strong><small>Sistem ${number(detail.stok_sistem_validasi)} · Mutasi +${number(detail.mutasi_masuk)} / -${number(detail.mutasi_keluar)}</small></div>`;
                if (comparison) {
                    return `<tr data-id="${detail.id}" data-counted="1" class="${differenceClass}">
                        <td><div class="item-main"><strong>${esc(detail.kode_obat || '-')} • ${esc(detail.nama_obat)}</strong><small><i class="mdi mdi-map-marker-outline"></i> ${esc(detail.rak)} &middot; ${esc(detail.satuan || '-')}</small></div></td>
                        <td><div class="item-main"><strong>${batchLabel}</strong><small>${expiryLabel}</small></div></td>
                        <td><strong>${number(detail.stok_sistem)} <small>${esc(detail.satuan || '')}</small></strong></td>
                        <td>${physicalCell}</td><td><span class="difference-pill ${differenceClass}">${differenceLabel}</span></td>
                        ${row.can_review ? `<td><div class="cost-cell"><strong>Rp ${money(detail.hpp)}</strong><small>Nilai ${detail.nilai_selisih > 0 ? '+' : ''}Rp ${money(detail.nilai_selisih)}</small></div></td>` : ''}
                        <td>${reconcileCell}</td><td>${reasonCell}</td></tr>`;
                }
                return `<tr data-id="${detail.id}" data-counted="${counted ? '1' : '0'}" class="${counted ? 'is-complete' : ''}">
                    <td><div class="item-main"><strong>${esc(detail.kode_obat || '-')} • ${esc(detail.nama_obat)}</strong><small><i class="mdi mdi-map-marker-outline"></i> ${esc(detail.rak)} &middot; ${esc(detail.satuan || '-')}</small></div></td>
                    <td><div class="item-main"><strong>${batchLabel}</strong><small>${expiryLabel}</small></div></td>
                    <td>${physicalCell}</td><td><span class="count-meta">${esc(detail.counted_by || 'Belum dihitung')}<br>${dateTime(detail.counted_at)}</span></td></tr>`;
            }).join(''));
            updateCountWorkspace();
        }

        function money(value) { return new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0, maximumFractionDigits: 0 }).format(Number(value || 0)); }

        function renderSummary(row) {
            const summary = row.summary;
            if (!summary) { $('#summaryContent').empty(); return; }
            const netLabel = Number(summary.net_value || 0) < 0 ? 'Kerugian Bersih' : 'Surplus Bersih';
            $('#summaryContent').html(`<div class="opname-posting-summary">
                <div class="posting-summary-hero"><div><small>RINGKASAN ${row.status === 'adjusted' ? 'HASIL POSTING' : 'SEMENTARA'}</small><strong>${esc(row.nomor)}</strong><span>${number(summary.total_items)} item dihitung · ${number(summary.matching_items)} item klop</span></div><span class="summary-net ${Number(summary.net_value || 0) < 0 ? 'is-loss' : 'is-surplus'}"><small>${netLabel}</small><strong>Rp ${money(Math.abs(summary.net_value))}</strong></span></div>
                <div class="posting-summary-grid">
                    <article class="is-danger"><small>Selisih Minus</small><strong>${number(summary.minus_qty)}</strong><span>${number(summary.minus_items)} item · Rp ${money(summary.loss_value)}</span></article>
                    <article class="is-success"><small>Selisih Plus</small><strong>+${number(summary.plus_qty)}</strong><span>${number(summary.plus_items)} item · Rp ${money(summary.surplus_value)}</span></article>
                    <article><small>Stok Sistem Awal</small><strong>${number(summary.system_qty)}</strong><span>Fisik ${number(summary.physical_qty)}</span></article>
                    <article><small>Mutasi Setelah Submit</small><strong>+${number(summary.movement_in_qty)} / -${number(summary.movement_out_qty)}</strong><span>Target akhir ${number(summary.target_qty)}</span></article>
                    <article><small>Sistem Saat Validasi</small><strong>${number(summary.current_system_qty)}</strong><span>Rekonsiliasi transaksi otomatis</span></article>
                    <article><small>Baris Diposting</small><strong>${number(summary.adjusted_rows || row.difference_count)}</strong><span>Masuk dan keluar ke kartu stok</span></article>
                </div></div>`);
        }

        function updateCountWorkspace(refreshFilter = true) {
            const rows = $('#countTableBody tr');
            let filled = 0;

            rows.each(function() {
                const input = $(this).find('.count-physical');
                const isFilled = input.length ? input.val() !== '' : $(this).data('counted') === 1;
                $(this).attr('data-counted', isFilled ? '1' : '0').toggleClass('is-complete', isFilled);
                if (isFilled) filled++;
            });

            $('#detailWorkspaceProgress').text(`${number(filled)}/${number(rows.length)} terisi`);
            $('#detailAllFilterCount').text(number(rows.length));
            $('#detailPendingFilterCount').text(number(rows.length - filled));
            $('#detailDifferenceFilterCount').text(number(rows.filter('.is-minus, .is-plus').length));
            setDetailProgress(filled, rows.length);
            if (refreshFilter) filterCountRows();
        }

        function filterCountRows() {
            const term = String($('#detailMedicineSearch').val() || '').trim().toLowerCase();
            const rowFilter = $('.opname-row-filter.is-active').data('row-filter') || 'all';
            const rows = $('#countTableBody tr');
            let visible = 0;

            rows.each(function() {
                const matchesTerm = term === '' || $(this).text().toLowerCase().includes(term);
                const matchesView = rowFilter === 'all'
                    || (rowFilter === 'pending' && $(this).attr('data-counted') !== '1')
                    || (rowFilter === 'difference' && ($(this).hasClass('is-minus') || $(this).hasClass('is-plus')));
                const show = matchesTerm && matchesView;
                $(this).toggleClass('d-none', !show);
                if (show) visible++;
            });

            $('#detailVisibleCount').text(`${number(visible)} dari ${number(rows.length)} item ditampilkan`);
            $('#detailCountNoResult').toggleClass('d-none', visible !== 0);
            $('.opname-count-table-wrap').toggleClass('d-none', visible === 0);
        }

        $('#detailMedicineSearch').on('input', function() {
            $(this).closest('.opname-detail-search').toggleClass('has-value', this.value.length > 0);
            filterCountRows();
        });
        $('#clearDetailMedicineSearch').on('click', function() { $('#detailMedicineSearch').val('').trigger('input').focus(); });
        $('.opname-row-filter').on('click', function() {
            $('.opname-row-filter').removeClass('is-active').attr('aria-pressed', 'false');
            $(this).addClass('is-active').attr('aria-pressed', 'true');
            filterCountRows();
        });
        function updateDirtyCountState() {
            let hasChanges = false;
            $('#countTableBody .count-physical, #countTableBody .reason-input').each(function() {
                const changed = String($(this).val()) !== String($(this).attr('data-original') || '');
                $(this).closest('tr').toggleClass('is-dirty', changed);
                if (changed) hasChanges = true;
            });
            $('#countTab').toggleClass('has-local-changes', hasChanges);
            $('#detailFooterNote').toggleClass('is-warning', hasChanges);
            $('#detailActions button[onclick]').not('[onclick^="saveOpname"]').prop('disabled', hasChanges);
            if (hasChanges) {
                $('#detailFooterNote > div > span').text('Ada perubahan pada workspace yang belum disimpan. Simpan sebelum menutup dokumen.');
            } else if (currentOpname) {
                renderActions(currentOpname);
            }
        }
        $('#countTableBody').on('input', '.count-physical, .reason-input', function() {
            if ($(this).hasClass('count-physical')) updateCountWorkspace(false);
            updateDirtyCountState();
        });
        $('#countTableBody').on('change', '.count-physical', filterCountRows);
        $('#countTableBody').on('focus', '.count-physical', function() { this.select(); });
        $('#detailFillZero').on('click', function() {
            const emptyRows = $('#countTableBody tr:not(.d-none)').filter(function() { return $(this).find('.count-physical').val() === ''; });
            if (!emptyRows.length) { Swal.fire('Sudah lengkap', 'Tidak ada stok fisik kosong pada daftar yang sedang ditampilkan.', 'info'); return; }
            Swal.fire({ title: 'Isi stok kosong dengan 0?', html: `<strong>${number(emptyRows.length)} item yang terlihat</strong> akan diisi nol.<br><small>Nilai belum disimpan sampai Anda menekan Simpan Stok Fisik.</small>`, icon: 'question', showCancelButton: true, confirmButtonText: 'Ya, Isi 0', cancelButtonText: 'Batal', reverseButtons: true })
                .then(result => {
                    if (!result.isConfirmed) return;
                    emptyRows.each(function() { $(this).find('.count-physical').val('0'); $(this).addClass('is-autofilled'); });
                    updateCountWorkspace();
                    updateDirtyCountState();
                    setTimeout(() => emptyRows.removeClass('is-autofilled'), 800);
                });
        });
        $('#opnameDetailModal').on('hide.bs.modal', function(event) {
            if (!$('#countTab').hasClass('has-local-changes')) return;
            event.preventDefault();
            toggleDetailModalFocusTrap(false);
            Swal.fire({
                title: 'Perubahan belum disimpan',
                text: 'Stok fisik atau alasan selisih yang baru Anda ubah akan hilang jika control document ditutup.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Tutup Tanpa Simpan',
                cancelButtonText: 'Kembali Menghitung',
                confirmButtonColor: '#dc2626',
                reverseButtons: true
            }).then(result => {
                toggleDetailModalFocusTrap(true);
                if (!result.isConfirmed) return;
                $('#countTab').removeClass('has-local-changes');
                $('#opnameDetailModal').modal('hide');
            });
        });

        function renderMovements(rows) {
            if (!rows.length) { $('#movementContent').html('<div class="opname-empty"><i class="mdi mdi-swap-horizontal"></i><strong>Belum ada transaksi setelah submit</strong><small>Mutasi stok dan kasir yang terjadi setelah blind count akan muncul otomatis di sini.</small></div>'); return; }
            $('#movementContent').html(`<div class="table-responsive"><table class="table opname-movement-table"><thead><tr><th>Waktu</th><th>Obat</th><th>Batch / ED</th><th>Jenis</th><th>Masuk</th><th>Keluar</th><th>Referensi</th><th>User</th></tr></thead><tbody>${rows.map(item => `<tr><td class="movement-time">${dateTime(item.occurred_at)}</td><td><div class="movement-identity"><strong>${esc(item.nama_obat || 'Obat tidak ditemukan')}</strong><small>${esc(item.kode_obat || '-')} &middot; ${esc(item.satuan || '-')}</small></div></td><td><div class="movement-batch"><strong>${esc(item.no_batch || '-')}</strong><small>ED ${date(item.expired_date)}</small></div></td><td>${esc(String(item.jenis_mutasi || '-').replaceAll('_', ' '))}</td><td class="text-success fw-bold">${number(item.qty_masuk)}</td><td class="text-danger fw-bold">${number(item.qty_keluar)}</td><td>${esc(item.reference || '-')}</td><td>${esc(item.user || '-')}</td></tr>`).join('')}</tbody></table></div>`);
        }

        function renderAudit(rows) {
            if (!rows.length) { $('#auditContent').html('<div class="opname-empty"><i class="mdi mdi-history"></i><strong>Belum ada aktivitas</strong></div>'); return; }
            $('#auditContent').html(rows.map(item => `<div class="opname-audit-item"><span class="opname-audit-icon"><i class="mdi mdi-check"></i></span><div><strong>${esc(actionLabels[item.action] || item.action.replaceAll('_', ' '))}</strong><p>${esc(item.note || '-')} · oleh ${esc(item.performed_by || 'Sistem')}</p></div><small>${dateTime(item.performed_at)}</small></div>`).join(''));
        }

        function renderActions(row) {
            $('#detailFooterNote').removeClass('is-warning');
            let actions = '<button type="button" class="btn btn-light" data-bs-dismiss="modal"><i class="mdi mdi-close"></i> Tutup</button>';
            let note = 'Dokumen hanya dapat diproses mengikuti urutan workflow.';
            if (row.status !== 'draft') actions += `<a class="btn btn-outline-secondary" href="${esc(row.print_url)}" target="_blank" rel="noopener"><i class="mdi mdi-printer-outline"></i> Cetak Lembar</a>`;
            if (row.report_url) actions += `<a class="btn btn-outline-danger" href="${esc(row.report_url)}"><i class="mdi mdi-file-pdf-box"></i> Unduh Laporan PDF</a>`;
            if (row.status === 'draft') {
                if (row.can_manage) actions += `<button class="btn btn-outline-secondary" onclick="editFromDetail()"><i class="mdi mdi-pencil-outline"></i> Edit</button><button class="btn btn-primary" onclick="startFromDetail()"><i class="mdi mdi-play-outline"></i> Otorisasi & Mulai</button>`;
                note = row.can_manage ? 'Mulai penghitungan untuk memberi akses input kepada user cabang.' : 'Menunggu otorisasi admin/apoteker.';
            }
            if (row.status === 'counting') {
                if (row.can_count) actions += `<button class="btn btn-outline-primary" onclick="saveOpnameCounts()"><i class="mdi mdi-content-save-outline"></i> Simpan Stok Fisik</button>`;
                if (row.can_submit) {
                    const countComplete = Number(row.details_count || 0) > 0 && Number(row.counted_count || 0) >= Number(row.details_count || 0);
                    actions += `<button class="btn btn-primary" onclick="submitStockOpname()" ${countComplete ? '' : 'disabled title="Lengkapi dan simpan seluruh stok fisik terlebih dahulu"'}><i class="mdi mdi-send-check-outline"></i> Submit Stok Fisik</button>`;
                }
                note = row.can_count
                    ? `${row.counted_count}/${row.details_count} item telah dihitung. Petugas dapat submit setelah seluruh stok fisik terisi.`
                    : `${row.counted_count}/${row.details_count} item telah dihitung.`;
            }
            if (row.status === 'awaiting_verification') {
                if (row.can_explain) actions += `<button class="btn btn-outline-primary" onclick="saveOpnameReasons()"><i class="mdi mdi-content-save-check-outline"></i> Simpan Alasan</button>`;
                if (row.can_reject) actions += `<button class="btn btn-outline-danger" onclick="rejectStockOpname()"><i class="mdi mdi-undo-variant"></i> Hitung Ulang</button>`;
                if (row.can_verify) actions += `<button class="btn btn-primary" onclick="verifyStockOpname()"><i class="mdi mdi-shield-check-outline"></i> Validasi & Rekonsiliasi</button>`;
                note = row.can_verify ? 'Petugas cabang dapat memvalidasi. Sistem akan merekonsiliasi transaksi kasir/stok setelah submit.' : 'Menunggu user/petugas cabang terkait.';
            }
            if (row.status === 'awaiting_approval') {
                if (row.can_reject) actions += `<button class="btn btn-outline-danger" onclick="rejectStockOpname()"><i class="mdi mdi-undo-variant"></i> Kembalikan</button>`;
                if (row.can_approve) actions += `<button class="btn btn-primary" onclick="approveStockOpname()"><i class="mdi mdi-check-decagram-outline"></i> Setujui</button>`;
                note = row.can_approve ? 'Persetujuan tidak langsung mengubah stok.' : 'Menunggu persetujuan admin/apoteker.';
            }
            if (row.status === 'approved') { if (row.can_adjust) actions += `<button class="btn btn-success" onclick="adjustStockOpname()"><i class="mdi mdi-database-sync-outline"></i> Posting Penyesuaian</button>`; note = row.can_adjust ? `${row.difference_count} item akan disesuaikan dan dicatat ke kartu stok.` : 'Menunggu penyetuju memposting penyesuaian stok.'; }
            if (row.status === 'adjusted') note = `Selesai disesuaikan ${dateTime(row.adjusted_at)} oleh ${row.adjusted_by || '-'}.`;
            $('#detailFooterNote > div > span').text(note); $('#detailActions').html(actions);
        }

        window.editFromDetail = function() { const id = currentOpname.id; $('#opnameDetailModal').modal('hide'); setTimeout(() => editStockOpname(id), 250); };
        window.startFromDetail = function() { startStockOpname(currentOpname.id); };
        window.saveOpnameCounts = function() {
            const details = [];
            $('#countTableBody tr').each(function() { const physical = $(this).find('.count-physical').val(); if (physical !== '') details.push({ id: Number($(this).data('id')), stok_fisik: physical }); });
            if (!details.length) { Swal.fire('Belum ada hitungan', 'Isi minimal satu stok fisik untuk disimpan.', 'warning'); return; }
            runAction(currentOpname.id, 'counts', { details });
        };
        window.saveOpnameReasons = function() {
            const details = [];
            $('#countTableBody tr').each(function() { const input = $(this).find('.reason-input'); if (input.length) details.push({ id: Number($(this).data('id')), alasan_selisih: input.val() }); });
            if (!details.length) { Swal.fire('Tidak ada selisih', 'Seluruh stok sudah klop dan tidak membutuhkan alasan.', 'info'); return; }
            runAction(currentOpname.id, 'reasons', { details });
        };
        window.submitStockOpname = function() { Swal.fire({ title: 'Submit stok fisik?', html: 'Setelah submit, stok sistem dan selisih langsung ditampilkan.<br><small>Modul Stok dan Kasir/POS juga langsung dibuka kembali.</small>', icon: 'question', showCancelButton: true, confirmButtonText: 'Submit & Buka Operasional', cancelButtonText: 'Batal', reverseButtons: true }).then(result => { if (result.isConfirmed) runAction(currentOpname.id, 'submit'); }); };
        window.verifyStockOpname = function() { noteAction('Validasi stock opname?', 'Catatan validasi (opsional)', 'Validasi & Rekonsiliasi', 'verify'); };
        window.approveStockOpname = function() { noteAction('Setujui stock opname?', 'Catatan persetujuan (opsional)', 'Setujui', 'approve'); };
        window.rejectStockOpname = function() { noteAction('Kembalikan ke penghitungan?', 'Alasan pengembalian wajib diisi', 'Kembalikan', 'reject', true); };
        window.adjustStockOpname = function() { const s = currentOpname.summary || {}; Swal.fire({ title: 'Posting penyesuaian stok?', html: `<div class="text-start"><strong>${currentOpname.difference_count} item</strong> akan direkonsiliasi ulang dan diposting.<hr><div>Selisih minus: <strong>${number(s.minus_qty)} / Rp ${money(s.loss_value)}</strong></div><div>Selisih plus: <strong>+${number(s.plus_qty)} / Rp ${money(s.surplus_value)}</strong></div><div class="mt-2">Nilai bersih: <strong>Rp ${money(s.net_value)}</strong></div><small class="text-muted">Transaksi setelah validasi tetap ikut dihitung sebelum posting.</small></div>`, icon: 'warning', showCancelButton: true, confirmButtonText: 'Posting Penyesuaian', cancelButtonText: 'Batal', confirmButtonColor: '#059669', reverseButtons: true }).then(result => { if (result.isConfirmed) runAction(currentOpname.id, 'adjust'); }); };

        function toggleDetailModalFocusTrap(activate) {
            if (!window.bootstrap || !bootstrap.Modal) return;

            const modalElement = document.getElementById('opnameDetailModal');
            const modalInstance = modalElement ? bootstrap.Modal.getInstance(modalElement) : null;
            const focusTrap = modalInstance?._focustrap;

            if (!focusTrap) return;

            if (activate && modalElement.classList.contains('show')) {
                focusTrap.activate();
                return;
            }

            focusTrap.deactivate();
        }

        function noteAction(title, placeholder, confirmText, action, required = false) {
            toggleDetailModalFocusTrap(false);

            Swal.fire({
                title,
                input: 'textarea',
                inputLabel: placeholder,
                inputPlaceholder: placeholder,
                showCancelButton: true,
                confirmButtonText: confirmText,
                cancelButtonText: 'Batal',
                reverseButtons: true,
                didOpen: () => {
                    const input = Swal.getInput();
                    if (!input) return;

                    input.removeAttribute('readonly');
                    input.removeAttribute('disabled');
                    input.focus();
                },
                preConfirm: value => { if (required && !value?.trim()) { Swal.showValidationMessage('Alasan wajib diisi.'); return false; } return value?.trim() || ''; }
            }).then(result => {
                toggleDetailModalFocusTrap(true);
                if (result.isConfirmed) runAction(currentOpname.id, action, { note: result.value });
            });
        }

        function runAction(id, action, data = {}, openDetailAfter = false) {
            Swal.fire({ title: 'Memproses...', allowOutsideClick: false, allowEscapeKey: false, didOpen: () => Swal.showLoading() });
            $.ajax({ url: url(routes[action], id), method: 'PUT', data })
                .done(response => {
                    table.ajax.reload(null, false); Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1500, showConfirmButton: false });
                    if ($('#opnameDetailModal').hasClass('show')) loadDetail(id); else if (openDetailAfter) setTimeout(() => viewStockOpname(id), 200);
                })
                .fail(xhr => ajaxError(xhr, 'Aksi stock opname gagal diproses.'));
        }
    });
</script>
