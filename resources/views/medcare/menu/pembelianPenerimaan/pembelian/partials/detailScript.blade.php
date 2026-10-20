        const $detailMedicineSearch = $('#detailMedicineSearch');

        $detailMedicineSearch.select2({
            placeholder: 'Cari nama atau kode obat...',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#pembelianModalDetail'),
            language: {
                noResults: () => 'Obat tidak ditemukan dalam PO ini',
                searching: () => 'Mencari obat...'
            }
        });

        function filterPurchaseOrderDetail() {
            const medicineId = String($detailMedicineSearch.val() || '');
            const $rows = $('#detailObatTable tbody tr');
            let visible = 0;
            $rows.each(function() {
                const matches = !medicineId || this.dataset.medicineId === medicineId;
                $(this).toggleClass('d-none', !matches);
                if (matches) visible++;
            });
            $('#detailMedicineResultCount').text(`Menampilkan ${visible} dari ${$rows.length} item obat`);
            $('#resetDetailMedicineSearch').prop('disabled', !medicineId);
        }

        $detailMedicineSearch.on('change', filterPurchaseOrderDetail);
        $('#resetDetailMedicineSearch').on('click', function() {
            $detailMedicineSearch.val(null).trigger('change');
        });
        $('#pembelianModalDetail').on('shown.bs.modal', function() {
            $(this).find('.modal-body').scrollTop(0);
        }).on('hide.bs.modal', function() {
            $detailMedicineSearch.select2('close');
        });

        function renderPurchaseOrderDetail(po) {
            const detail = po.details;
            const statuses = {
                draft: ['Draft', 'is-draft', 'mdi-file-edit-outline'],
                waiting_approval: ['Menunggu Approval', 'is-draft', 'mdi-clock-outline'],
                approved: ['Disetujui', 'is-approved', 'mdi-check-circle-outline'],
                dalam_penerimaan: ['Dalam Penerimaan', 'is-partial', 'mdi-package-variant'],
                diterima_sebagian: ['Diterima Sebagian', 'is-partial', 'mdi-progress-check'],
                selesai: ['Selesai', 'is-approved', 'mdi-package-variant-closed-check'],
                rejected: ['Ditolak', 'is-rejected', 'mdi-close-circle-outline']
            };
            const status = statuses[String(po.status || '').toLowerCase()] || [po.status || 'Belum tersedia', 'is-other', 'mdi-information-outline'];

            $('#detail_no_po').text(po.no_po || '—');
            $('#detail_distributor').text(po.distributor_name || po.distributor?.nama || 'Tidak diketahui');
            $('#detail_tanggal_po').text(po.tanggal_po ? moment(po.tanggal_po).toDate().toLocaleDateString('id-ID', {
                day: '2-digit', month: 'long', year: 'numeric'
            }) : '—');
            $('#detail_branch').text(po.branch?.name || '—');
            $('#detail_catatan').text(String(po.catatan || '').trim() || 'Belum ada catatan untuk pesanan ini.');
            $('#detail_status').attr('class', `purchase-status ${status[1]}`)
                .html(`<i class="mdi ${status[2]}" aria-hidden="true"></i>${escapeHtml(status[0])}`);
            $('#detail_total_item').text(detail.length);

            // Search this PO only, including historical medicines and repeated items in different units.
            const medicineOptions = new Map();
            const rows = document.createDocumentFragment();
            let medicineSubtotal = 0;
            detail.forEach((item, index) => {
                const medicineId = String(item.obat_id ?? item.obat?.id ?? `row-${index}`);
                const name = item.nama_obat || item.obat?.nama_obat || 'Obat tidak diketahui';
                const code = item.obat?.kode_obat || item.kode_obat || '';
                const unitName = item.satuan_konversi?.satuan?.nama || item.obat?.satuan?.nama || '—';
                const classifications = [
                    ['is_regular', 'Reguler', 'is-regular'], ['is_narcotic', 'Narkotika', 'is-narcotic'],
                    ['is_psychotropic', 'Psikotropika', 'is-psychotropic'], ['is_precursor', 'Prekursor', 'is-precursor'],
                    ['is_oot', 'OOT', 'is-oot']
                ].filter(([key]) => item[key]).map(([, label, className]) =>
                    `<span class="badge purchase-sp-badge ${className}">${label}</span>`
                ).join('');
                const discounts = [1, 2, 3].map(tier =>
                    `<span title="Diskon ${tier}">${Number(item[`diskon_${tier}`] || 0).toLocaleString('id-ID')}%</span>`
                ).join('<span class="purchase-detail-discount-divider" aria-hidden="true">/</span>');
                const row = document.createElement('tr');
                row.dataset.medicineId = medicineId;
                row.innerHTML = `
                    <td data-label="Nama Obat"><div class="purchase-detail-medicine"><strong>${escapeHtml(name)}</strong>
                        ${code ? `<small>${escapeHtml(code)}</small>` : ''}
                        ${classifications ? `<div class="purchase-detail-classifications">${classifications}</div>` : ''}</div></td>
                    <td data-label="Satuan"><span class="purchase-detail-unit">${escapeHtml(unitName)}</span></td>
                    <td data-label="Qty" class="purchase-detail-numeric text-end">${Number(item.qty || 0).toLocaleString('id-ID')}</td>
                    <td data-label="Harga Estimasi" class="purchase-detail-money text-end">${formatRupiah(item.harga_estimasi)}</td>
                    <td data-label="Diskon 1 / 2 / 3"><div class="purchase-detail-discounts">${discounts}</div></td>
                    <td data-label="PPN" class="purchase-detail-numeric text-end">${Number(item.ppn || 0).toLocaleString('id-ID')}%</td>
                    <td data-label="Subtotal + PPN" class="purchase-detail-line-total text-end">${formatRupiah(item.subtotal)}</td>
                `;
                rows.appendChild(row);
                medicineOptions.set(medicineId, code ? `${name} · ${code}` : name);
                medicineSubtotal += Number(item.subtotal || 0);
            });

            $('#detailObatTable tbody').empty().append(rows);
            $detailMedicineSearch.select2('close').empty().append(new Option('', ''));
            medicineOptions.forEach((label, id) => $detailMedicineSearch.append(new Option(label, id)));
            $detailMedicineSearch.prop('disabled', detail.length === 0).val(null).trigger('change');
            $('#detailMedicineEmpty').toggleClass('d-none', detail.length > 0);
            $('#detailObatTable').toggleClass('d-none', detail.length === 0);
            // Saved prices already include the purchase-unit conversion; use each item's own tax rate.
            const summary = PurchaseOrderEntry.summarizeAmounts(detail.map(item =>
                PurchaseOrderEntry.calculateAmounts({
                    qty: item.qty,
                    price: item.harga_estimasi,
                    discounts: [item.diskon_1, item.diskon_2, item.diskon_3],
                    tax: item.ppn ?? 0
                })
            ));
            $('#detail_subtotal_awal').text(formatRupiah(summary.gross));
            $('#detail_total_diskon').text((summary.discount > 0 ? '\u2212 ' : '') + formatRupiah(summary.discount));
            $('#detail_subtotal_netto').text(formatRupiah(summary.gross - summary.discount));
            $('#detail_total_ppn').text(formatRupiah(summary.tax));
            $('#detail_subtotal_obat').text(formatRupiah(medicineSubtotal));
            $('#detail_biaya_asuransi').text(formatRupiah(po.biaya_asuransi));
            $('#detail_biaya_pengiriman').text(formatRupiah(po.biaya_pengiriman));
            $('#detail_total_estimasi').text(formatRupiah(po.total_estimasi));
        }
