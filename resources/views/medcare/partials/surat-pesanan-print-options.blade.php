<script>
    window.showSuratPesananPrintOptions = function({ documents, target = 'body' }) {
        if (!documents.length) return;

        const escapeHtml = function(value) {
            const element = document.createElement('div');
            element.textContent = value;
            return element.innerHTML;
        };
        const documentField = documents.length > 1
            ? `<label for="spPrintDocument">Jenis Surat Pesanan</label>
                <select id="spPrintDocument" class="swal2-select" style="display:block;width:100%;margin:8px 0 16px">
                    <option value="">Pilih jenis SP</option>
                    ${documents.map((item, index) => `<option value="${index}">${escapeHtml(item.name)}</option>`).join('')}
                </select>`
            : `<p>${escapeHtml(documents[0].name || 'Surat Pesanan')}</p>`;

        return Swal.fire({
            target,
            title: 'Pilihan Cetak SP',
            html: documentField,
            input: 'select',
            inputLabel: 'Tanda tangan apoteker',
            inputOptions: new Map([['1', 'Dengan TTD'], ['0', 'Tanpa TTD']]),
            inputValue: '1',
            showCancelButton: true,
            confirmButtonText: 'Cetak Surat',
            cancelButtonText: 'Batal',
            keydownListenerCapture: true,
            preConfirm: function(withSignature) {
                const selectedIndex = documents.length > 1
                    ? Swal.getHtmlContainer().querySelector('#spPrintDocument').value
                    : '0';
                const selectedDocument = selectedIndex === '' ? null : documents[selectedIndex];

                if (!selectedDocument) {
                    Swal.showValidationMessage('Pilih jenis surat pesanan terlebih dahulu.');
                    return false;
                }

                const printUrl = new URL(selectedDocument.url, window.location.origin);
                printUrl.searchParams.set('print', '1');
                printUrl.searchParams.set('with_signature', withSignature);
                const printWindow = window.open(printUrl.toString(), '_blank');

                if (!printWindow) {
                    Swal.showValidationMessage('Izinkan pop-up untuk situs ini agar surat pesanan dapat dibuka.');
                    return false;
                }

                printWindow.opener = null;
            }
        });
    };
</script>
