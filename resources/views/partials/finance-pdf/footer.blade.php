<div class="signs-wrap">
<table class="signs">
    <tr>
        <td>
            <div class="sig-ttl">توقيع الشركة / Company signature</div>
            <div class="stamp-box">
                @if(!empty($signatureDataUri))
                    <img src="{{ $signatureDataUri }}" alt="">
                @endif
            </div>
            @if(!empty($signatureName))
                <div class="sig-name">{{ $signatureName }}</div>
            @endif
            <div class="sig-line">التاريخ : <span class="en">{{ $dateLabel }}</span></div>
        </td>
        <td>
            <div class="sig-ttl">توقيع العميل / Client signature</div>
            <div class="stamp-box">&nbsp;</div>
            @if(!empty($clientName) && $clientName !== '—')
                <div class="sig-name {{ !empty($clientNameIsArabic) ? 'ar' : '' }}">{{ $clientName }}</div>
            @endif
            <div class="sig-line">التاريخ :</div>
        </td>
    </tr>
</table>
</div>

<div class="footer">
    <div class="thanks-bar">شكراً لتعاملكم معنا</div>
    @if(!empty($waveSvg))
    <div class="wave-wrap">
        <img src="{{ $waveSvg }}" alt="">
    </div>
    @endif
</div>
