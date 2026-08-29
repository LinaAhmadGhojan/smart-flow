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

    <table class="grid">
        @if(empty($forPdf))
        <colgroup>
            <col style="width:26%">
            <col style="width:30%">
            <col style="width:22%">
            <col style="width:22%">
        </colgroup>
        @endif
        <tr>
            @if(!empty($forPdf))
            <th class="arb" style="width:22%">{{ $lblDateCol }}</th>
            <th class="arb" style="width:22%">{{ $lblBank }}</th>
            <th class="arb" style="width:30%">{{ $lblRef }}</th>
            <th class="arb" style="width:26%">{{ $lblAmount }}</th>
            @else
            <th class="arb">{{ $lblAmount }}</th>
            <th class="arb">{{ $lblRef }}</th>
            <th class="arb">{{ $lblBank }}</th>
            <th class="arb">{{ $lblDateCol }}</th>
            @endif
        </tr>
        <tr>
            @if(!empty($forPdf))
            <td><span class="en">{{ $dateLabel }}</span></td>
            <td>@if($bankName)<span class="ar">{{ $bankName }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td>@if($refNo)<span class="en">{{ $refNo }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td class="amt-cell">
                <span class="en amt-num">{{ $amountNumber }}</span>
                <span class="arb amt-cur">{{ $lblCurrency }}</span>
            </td>
            @else
            <td class="amt-cell">
                <span class="en amt-num">{{ $amountNumber }}</span>
                <span class="arb amt-cur">{{ $lblCurrency }}</span>
            </td>
            <td>@if($refNo)<span class="en">{{ $refNo }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td>@if($bankName)<span class="ar">{{ $bankName }}</span>@else<span class="cell-dots"></span>@endif</td>
            <td><span class="en">{{ $dateLabel }}</span></td>
            @endif
        </tr>
    </table>

    <table class="signs">
        <tr>
            @if(!empty($forPdf))
            <td>
                <div class="sig-lab arb">{{ $lblReceiver }}</div>
                <div class="sig-line"></div>
            </td>
            <td></td>
            <td>
                <div class="sig-lab arb">{{ $lblAccountant }}</div>
                <div class="sig-line"></div>
            </td>
            @else
            <td>
                <div class="sig-lab arb">{{ $lblAccountant }}</div>
                <div class="sig-line"></div>
            </td>
            <td></td>
            <td>
                <div class="sig-lab arb">{{ $lblReceiver }}</div>
                <div class="sig-line"></div>
            </td>
            @endif
        </tr>
    </table>
</div>

<div class="thanks arb">{{ $lblThanks }}</div>
<div class="contact">
    <span><img src="{{ $iconPhone }}" alt=""> <span class="en" dir="ltr">{{ $phone }}</span></span>
    <span><img src="{{ $iconEmail }}" alt=""> <span class="en" dir="ltr">{{ $email }}</span></span>
    <span><img src="{{ $iconLocation }}" alt=""> <span class="ar">{{ $addressAr }}</span></span>
</div>
<div class="wave-wrap" aria-hidden="true">
    <img src="{{ $waveSvg }}" alt="">
</div>
