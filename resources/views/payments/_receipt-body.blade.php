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

    <div class="pay-wrap">
        <div class="pay-box">
            <div class="pay-tab-row"><span class="pay-tab arb">{{ $lblPayMethod }}</span></div>
            <table class="pay-grid">
                <tr>
                    {{-- Visual LTR matches web RTL screen: Cash | Bank | Cheque --}}
                    <td>
                        <table class="pay-item"><tr>
                            <td class="pi-ico"><img class="pay-icon" src="{{ $iconCash }}" alt=""></td>
                            <td class="pi-lbl"><span class="pay-lbl arb">{{ $lblCash }}</span></td>
                            <td class="pi-chk"><span class="chk {{ $isCash ? 'on' : '' }}">{{ $isCash ? '✓' : '' }}</span></td>
                        </tr></table>
                    </td>
                    <td>
                        <table class="pay-item"><tr>
                            <td class="pi-ico"><img class="pay-icon" src="{{ $iconBank }}" alt=""></td>
                            <td class="pi-lbl"><span class="pay-lbl arb">{{ $lblBankTransfer }}</span></td>
                            <td class="pi-chk"><span class="chk {{ $isBank ? 'on' : '' }}">{{ $isBank ? '✓' : '' }}</span></td>
                        </tr></table>
                    </td>
                    <td>
                        <table class="pay-item"><tr>
                            <td class="pi-ico"><img class="pay-icon" src="{{ $iconCheque }}" alt=""></td>
                            <td class="pi-lbl"><span class="pay-lbl arb">{{ $lblCheque }}</span></td>
                            <td class="pi-chk"><span class="chk {{ $isCheque ? 'on' : '' }}">{{ $isCheque ? '✓' : '' }}</span></td>
                        </tr></table>
                    </td>
                </tr>
            </table>
        </div>
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
                <span class="arb">{{ $lblCurrency }}</span>
                <span class="en"> {{ $amountNumber }}</span>
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
<table class="contact">
    <tr>
        <td>
            <img src="{{ $iconPhone }}" alt="">
            <span class="en">{{ $phone }}</span>
        </td>
        <td>
            <img src="{{ $iconEmail }}" alt="">
            <span class="en">{{ $email }}</span>
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
