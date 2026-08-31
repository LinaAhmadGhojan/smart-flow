<table class="hdr">
    <tr>
        <td class="hdr-brand">
            <table class="hdr-inner">
                <tr>
                    <td class="logo-cell">
                        @if(!empty($logoDataUri))
                            <img class="logo" src="{{ $logoDataUri }}" alt="">
                        @endif
                    </td>
                    <td>
                        <div class="brand-name ar">{{ $companyNameAr }}</div>
                        <div class="brand-row"><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></div>
                        <div class="brand-row"><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></div>
                        <div class="brand-row"><img src="{{ $iconLocation }}" alt=""><span class="ar">{{ $addressAr }}</span></div>
                        @if(!empty($trn))
                            <div class="trn-line en">TRN: {{ $trn }}</div>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
        <td class="hdr-doc">
            <div class="doc-ar">{{ $docTitleAr }}</div>
            <div class="doc-en">{{ $docTitleEn }}</div>
            <table class="meta-pair">
                <tr>
                    <td class="meta-lab">{{ $numberLabelAr }}</td>
                    <td class="meta-box"><span class="en">{{ $docNumber }}</span></td>
                </tr>
                <tr>
                    <td class="meta-lab">التاريخ</td>
                    <td class="meta-box"><span class="en">{{ $dateLabel }}</span></td>
                </tr>
            </table>
            @if(!empty($extraMetaRows))
            <table class="meta-extra">
                @foreach($extraMetaRows as $row)
                <tr>
                    <td class="lab">{{ $row['label'] }}</td>
                    <td class="val">{{ $row['value'] }}</td>
                </tr>
                @endforeach
            </table>
            @endif
        </td>
    </tr>
</table>

<table class="client-card">
    <tr><td class="client-h">{{ $clientLabelAr ?? 'العميل' }}</td></tr>
    <tr><td class="client-b {{ !empty($clientNameIsArabic) ? 'ar' : '' }}">{{ $clientName }}</td></tr>
</table>
