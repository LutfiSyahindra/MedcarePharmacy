<div class="signature-space{{ ($withSignature ?? true) && $signatureUrl ? ' has-signature' : '' }}">
    @if (($withSignature ?? true) && $signatureUrl)
        <img class="pharmacist-signature-image" src="{{ $signatureUrl }}" alt="Tanda tangan Apoteker">
    @endif
</div>
