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
                    <td class="val en">{{ $receiptNumber }}</td>
                    <td class="lab"><span class="arb">{{ $lblReceiptNo }}</span>&nbsp;:</td>
                </tr>
                <tr>
                    <td class="val en">{{ $dateLabel }}</td>
                    <td class="lab"><span class="arb">{{ $lblDate }}</span>&nbsp;:</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="sheet">
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

    {{-- No position:absolute — Dompdf reflows it and can force a 2nd page --}}
    <div class="pay-wrap">
        <div class="pay-tab-center"><span class="pay-tab arb">{{ $lblPayMethod }}</span></div>
        <table class="pay-grid">
            <tr>
                <td>
                    <img class="pay-icon" src="{{ $iconCash }}" alt="">
                    <div class="pay-label-row">
                        <span class="pay-lbl arb">{{ $lblCash }}</span>
                        <span class="chk {{ $isCash ? 'on' : '' }}">{{ $isCash ? '✓' : '' }}</span>
                    </div>
                </td>
                <td>
                    <img class="pay-icon" src="{{ $iconBank }}" alt="">
                    <div class="pay-label-row">
                        <span class="pay-lbl arb">{{ $lblBankTransfer }}</span>
                        <span class="chk {{ $isBank ? 'on' : '' }}">{{ $isBank ? '✓' : '' }}</span>
                    </div>
                </td>
                <td>
                    <img class="pay-icon" src="{{ $iconCheque }}" alt="">
                    <div class="pay-label-row">
                        <span class="pay-lbl arb">{{ $lblCheque }}</span>
                        <span class="chk {{ $isCheque ? 'on' : '' }}">{{ $isCheque ? '✓' : '' }}</span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

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
                <span class="en">{{ $amountNumber }}</span>
                <span class="arb"> {{ $lblCurrency }}</span>
            </td>
        </tr>
    </table>

    <table class="signs">
        <tr>
            <td>
                <div class="sig-lab arb">{{ $lblReceiver }}</div>
                <div class="sig-line"></div>
            </td>
            <td>
                <div class="sig-lab arb">{{ $lblAccountant }}</div>
                <div class="sig-line"></div>
            </td>
        </tr>
    </table>
</div>

<div class="thanks arb">{{ $lblThanks }}</div>
<div class="contact">
    <span><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></span>
    <span><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></span>
    <span><img src="{{ $iconLocation }}" alt=""> <span class="ar">{{ $addressAr }}</span></span>
</div>
<div class="footer-bar" aria-hidden="true"></div>
