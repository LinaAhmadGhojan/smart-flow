<table class="header">
    <tr>
        <td class="logo-cell">
            @if(!empty($logoDataUri))
                <img src="{{ $logoDataUri }}" alt="">
            @endif
            <div class="logo-name arb">{{ $companyNameAr }}</div>
        </td>
        <td class="title-cell">
            <div class="doc-title arb">{{ $lblTitle }}</div>
            <table class="meta">
                <tr>
                    <td class="lab"><span class="arb">{{ $lblReceiptNo }}</span>&nbsp;:</td>
                    <td class="val en">{{ $receiptNumber }}</td>
                </tr>
                <tr>
                    <td class="lab"><span class="arb">{{ $lblDate }}</span>&nbsp;:</td>
                    <td class="val en">{{ $dateLabel }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="sheet">
    @if(!empty($forPdf))
    <table class="fline">
        <tr>
            <td class="val ar">{{ $clientName }}</td>
            <td class="lab"><span class="arb">{{ $lblReceivedFrom }}</span>&nbsp;:</td>
        </tr>
    </table>
    <table class="fline">
        <tr>
            <td class="val ar">{{ $amountWords }}</td>
            <td class="lab"><span class="arb">{{ $lblAmountWords }}</span>&nbsp;:</td>
        </tr>
    </table>
    <table class="fline">
        <tr>
            <td class="val ar">{{ $forLabel }}</td>
            <td class="lab"><span class="arb">{{ $lblFor }}</span>&nbsp;:</td>
        </tr>
    </table>
    @else
    <div class="fline">
        <div class="lab"><span class="arb">{{ $lblReceivedFrom }}</span>&nbsp;:</div>
        <div class="val ar">{{ $clientName }}</div>
    </div>
    <div class="fline">
        <div class="lab"><span class="arb">{{ $lblAmountWords }}</span>&nbsp;:</div>
        <div class="val ar">{{ $amountWords }}</div>
    </div>
    <div class="fline">
        <div class="lab"><span class="arb">{{ $lblFor }}</span>&nbsp;:</div>
        <div class="val ar">{{ $forLabel }}</div>
    </div>
    @endif
    <div class="pay-wrap">
        <div class="pay-box">
            <div class="pay-tab arb">{{ $lblPayMethod }}</div>
            <table class="pay-grid">
                <tr>
                    @if(!empty($forPdf))
                    <td>
                        <div class="pay-item">
                            <img class="pay-icon" src="{{ $iconCash }}" alt="">
                            <span class="pay-lbl arb">{{ $lblCash }}</span>
                            <span class="chk {{ $isCash ? 'on' : '' }}">{{ $isCash ? '✓' : '' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="pay-item">
                            <img class="pay-icon" src="{{ $iconBank }}" alt="">
                            <span class="pay-lbl arb">{{ $lblBankTransfer }}</span>
                            <span class="chk {{ $isBank ? 'on' : '' }}">{{ $isBank ? '✓' : '' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="pay-item">
                            <img class="pay-icon" src="{{ $iconCheque }}" alt="">
                            <span class="pay-lbl arb">{{ $lblCheque }}</span>
                            <span class="chk {{ $isCheque ? 'on' : '' }}">{{ $isCheque ? '✓' : '' }}</span>
                        </div>
                    </td>
                    @else
                    <td>
                        <div class="pay-item">
                            <span class="chk {{ $isCheque ? 'on' : '' }}">{{ $isCheque ? '✓' : '' }}</span>
                            <span class="pay-lbl arb">{{ $lblCheque }}</span>
                            <img class="pay-icon" src="{{ $iconCheque }}" alt="">
                        </div>
                    </td>
                    <td>
                        <div class="pay-item">
                            <span class="chk {{ $isBank ? 'on' : '' }}">{{ $isBank ? '✓' : '' }}</span>
                            <span class="pay-lbl arb">{{ $lblBankTransfer }}</span>
                            <img class="pay-icon" src="{{ $iconBank }}" alt="">
                        </div>
                    </td>
                    <td>
                        <div class="pay-item">
                            <span class="chk {{ $isCash ? 'on' : '' }}">{{ $isCash ? '✓' : '' }}</span>
                            <span class="pay-lbl arb">{{ $lblCash }}</span>
                            <img class="pay-icon" src="{{ $iconCash }}" alt="">
                        </div>
                    </td>
                    @endif
                </tr>
            </table>
        </div>
    </div>

    @if(!empty($forPdf))
    <table class="grid">
        <tr>
            <th class="arb" style="width:22%">{{ $lblDateCol }}</th>
            <th class="arb" style="width:22%">{{ $lblBank }}</th>
            <th class="arb" style="width:30%">{{ $lblRef }}</th>
            <th class="arb" style="width:26%">{{ $lblAmount }}</th>
        </tr>
        <tr>
            <td><span class="en">{{ $dateLabel }}</span></td>
            <td>@if($bankName)<span class="ar">{{ $bankName }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td>@if($refNo)<span class="en">{{ $refNo }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td class="amt-cell">
                <span class="en amt-num">{{ $amountNumber }}</span>
                <span class="arb amt-cur">{{ $lblCurrency }}</span>
            </td>
        </tr>
    </table>
    @else
    <div class="amt-grid">
        <div class="amt-grid-head">
            <div class="arb">{{ $lblAmount }}</div>
            <div class="arb">{{ $lblRef }}</div>
            <div class="arb">{{ $lblBank }}</div>
            <div class="arb">{{ $lblDateCol }}</div>
        </div>
        <div class="amt-grid-row">
            <div class="amt-cell">
                <span class="en amt-num">{{ $amountNumber }}</span>
                <span class="arb amt-cur">{{ $lblCurrency }}</span>
            </div>
            <div>@if($refNo)<span class="en">{{ $refNo }}</span>@else<span class="cell-dots"></span>@endif</div>
            <div>@if($bankName)<span class="ar">{{ $bankName }}</span>@else<span class="cell-dots"></span>@endif</div>
            <div><span class="en">{{ $dateLabel }}</span></div>
        </div>
    </div>
    @endif

    @if(!empty($forPdf))
    <table class="signs">
        <tr>
            <td>
                <div class="sig-lab arb">{{ $lblReceiver }}</div>
                <div class="sig-line"></div>
            </td>
            <td></td>
            <td>
                <div class="sig-lab arb">{{ $lblAccountant }}</div>
                <div class="sig-line"></div>
            </td>
        </tr>
    </table>
    @else
    <div class="signs-row">
        <div class="sign-col">
            <div class="sig-lab arb">{{ $lblAccountant }}</div>
            <div class="sig-line"></div>
        </div>
        <div class="sign-col"></div>
        <div class="sign-col">
            <div class="sig-lab arb">{{ $lblReceiver }}</div>
            <div class="sig-line"></div>
        </div>
    </div>
    @endif
</div>

<div class="thanks arb">{{ $lblThanks }}</div>
<table class="contact" dir="ltr">
    <tr>
        <td>
            <img src="{{ $iconPhone }}" alt="">
            <span class="en" dir="ltr">{{ $phone }}</span>
        </td>
        <td>
            <img src="{{ $iconEmail }}" alt="">
            <span class="en" dir="ltr">{{ $email }}</span>
        </td>
        <td>
            <img src="{{ $iconLocation }}" alt="">
            <span class="ar">{{ $addressAr }}</span>
        </td>
    </tr>
</table>
<div class="wave-wrap" aria-hidden="true">
    <img src="{{ $waveSvg }}" alt="">
</div>
