<label for="spPrintSignature" class="sp-signature-control">
    Tanda tangan:
    <select id="spPrintSignature">
        <option value="1" @selected($withSignature ?? true)>Dengan TTD</option>
        <option value="0" @selected(! ($withSignature ?? true))>Tanpa TTD</option>
    </select>
</label>
<button type="button" onclick="window.print()">Cetak / Simpan PDF</button>
<script>
    document.getElementById('spPrintSignature').addEventListener('change', function() {
        const previewUrl = new URL(window.location.href);
        previewUrl.searchParams.set('with_signature', this.value);
        previewUrl.searchParams.delete('print');
        window.location.replace(previewUrl.toString());
    });
</script>
