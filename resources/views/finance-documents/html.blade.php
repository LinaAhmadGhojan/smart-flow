<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
<meta charset="utf-8"/>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
<title>{{ $docNumber }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet"/>
@if(!empty($fontEmbedCss))
<style>{!! $fontEmbedCss !!}</style>
@endif
<style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    html, body {
        background: #fff;
        color: #333;
        font-family: 'Cairo', 'CairoFallback', 'Segoe UI', sans-serif;
        font-size: 11px;
        line-height: 1.4;
        -webkit-font-smoothing: antialiased;
    }
    .ar {
        direction: rtl;
        unicode-bidi: embed;
        font-family: 'Cairo', 'CairoFallback', sans-serif;
    }
    .en { direction: ltr; unicode-bidi: embed; display: inline-block; }

    .page-wrap {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        align-items: center;
        background: #f4f7fb;
        padding: 16px;
        gap: 16px;
    }
    .sheet {
        width: 794px;
        max-width: 100%;
        min-height: 1123px;
        background: #fff;
        padding: 12px 36px 24px;
        position: relative;
        overflow: visible;
    }
    .sheet-summary {
        padding: 90px 36px 78px 36px;
        display: flex;
        flex-direction: column;
    }
    .summary-content {
        flex: 1;
    }
    body.fd-capture .page-wrap {
        min-height: auto !important;
        display: block !important;
        background: #fff !important;
        padding: 0 !important;
        gap: 0 !important;
    }
    body.fd-capture .sheet {
        width: 794px !important;
        max-width: 794px !important;
        overflow: visible !important;
        margin: 0 auto;
        box-shadow: none !important;
        min-height: 1123px !important;
    }

    .watermark {
        position: absolute;
        top: 280px;
        left: 10px;
        width: 320px;
        opacity: 0.07;
        z-index: 0;
        pointer-events: none;
    }
    .watermark img {
        width: 300px;
        height: auto;
        max-height: 300px;
        object-fit: contain;
    }

    .header {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 6px;
        direction: ltr;
        table-layout: fixed;
    }
    .header td { vertical-align: middle; border: none; padding: 0; }
    .header-brand {
        width: 32%;
        padding: 0 14px 0 0;
        vertical-align: middle !important;
    }
    .header-brand-inner {
        display: inline-block;
        width: auto;
        max-width: 100%;
        text-align: left;
    }
    .brand-head { margin-bottom: 4px; }
    .brand-head .brand-name {
        font-size: 18px;
        font-weight: 700;
        color: #1a437f;
        line-height: 1.05;
        text-align: left;
        direction: rtl;
        margin: 0;
        white-space: nowrap;
    }
    .brand-head .brand-name-en {
        font-size: 10px;
        font-weight: 700;
        color: #1a437f;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        text-align: left;
        margin: 2px 0 0;
        line-height: 1.1;
    }
    .brand-head-line {
        height: 1px;
        background: #1a437f;
        margin: 3px 0 6px;
        width: 100%;
    }
    .header-contact-rows {
        font-size: 10px;
        color: #1a437f;
        font-weight: 600;
        line-height: 1.2;
    }
    .header-contact-rows .row {
        display: flex;
        align-items: center;
        justify-content: flex-start;
        gap: 7px;
        margin: 0;
        white-space: nowrap;
        direction: ltr;
    }
    .header-contact-rows .row + .row { margin-top: 3px; }
    .header-contact-rows img { width: 16px; height: 16px; flex-shrink: 0; }

    .header-center {
        width: 32%;
        text-align: center;
        vertical-align: middle !important;
        padding: 0 12px;
        border-left: 1.5px solid #d0d8e4;
        border-right: 1.5px solid #d0d8e4;
    }
    .header-center img.logo {
        width: 152px;
        max-width: 152px;
        max-height: 140px;
        height: auto;
        display: block;
        object-fit: contain;
        margin: 0 auto;
    }

    .doc-cell {
        width: 36%;
        direction: rtl;
        padding: 0;
        vertical-align: middle !important;
        text-align: right;
    }
    .doc-right {
        display: inline-block;
        width: auto;
        max-width: 100%;
        margin-left: auto;
        margin-right: 0;
        text-align: right;
    }
    .doc-head { margin-bottom: 4px; }
    .doc-head .ar-title {
        font-size: 18px; font-weight: 700; color: #1a437f;
        line-height: 1.05; text-align: right; margin: 0;
        white-space: nowrap;
    }
    .doc-head .en-title {
        font-size: 10px; font-weight: 700; letter-spacing: 0.1em;
        color: #1a437f; text-align: right; margin: 2px 0 0;
        line-height: 1.1;
    }
    .doc-head-line {
        height: 1px;
        background: #1a437f;
        margin: 3px 0 4px;
        width: 100%;
    }
    .meta-box {
        display: inline-block;
        width: auto;
        max-width: 100%;
        background: transparent;
        border: none;
        border-radius: 0;
        padding: 0;
    }
    .meta-line {
        display: flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 14px;
        padding: 5px 0;
        border-bottom: 1px solid #d8e4f0;
        direction: rtl;
        white-space: nowrap;
    }
    .meta-line:last-child { border-bottom: none; }
    .meta-lab {
        font-size: 10px;
        font-weight: 600;
        color: #333;
        text-align: right;
        flex: 0 0 auto;
    }
    .meta-val {
        font-size: 10px;
        font-weight: 700;
        color: #1a437f;
        direction: ltr;
        text-align: left;
        flex: 0 0 auto;
    }
    .meta-val.ar { direction: rtl; unicode-bidi: embed; }

    .client-for-bar {
        width: 100%;
        margin-top: 10px;
        margin-bottom: 2px;
        position: relative;
        z-index: 1;
        text-align: right;
        direction: rtl;
    }
    .client-for-bar .meta-line {
        display: inline-flex;
        width: auto;
        max-width: 100%;
        justify-content: flex-start;
        gap: 8px;
        padding: 6px 0 8px;
        border-bottom: 1px solid #d8e4f0;
    }

    .items {
        width: 100%;
        border-collapse: collapse;
        margin-top: 0;
        margin-bottom: 14px;
        position: relative;
        z-index: 1;
        table-layout: fixed;
        border: none;
    }
    .items th {
        background: #1a437f;
        color: #fff;
        border: none;
        border-top: 1.5px solid #1a437f;
        border-bottom: 1.5px solid #1a437f;
        text-align: center;
        padding: 9px 6px 8px;
        font-size: 10.5px;
        font-weight: 700;
        white-space: nowrap;
        line-height: 1.2;
        vertical-align: middle;
    }
    .items td {
        border: none;
        border-bottom: 1px solid #e2eaf4;
        padding: 10px 6px;
        vertical-align: middle;
        color: #1a437f;
        font-size: 10.5px;
        line-height: 1.35;
        word-wrap: break-word;
        overflow-wrap: anywhere;
    }
    .items tbody tr:nth-child(even) td { background: #f8fbff; }
    .items tbody tr:last-child td { border-bottom: 1px solid #b8cce8; }
    .items .code { text-align: center; white-space: nowrap; font-weight: 600; }
    .items .desc { text-align: left; vertical-align: top; }
    .items .img-cell { text-align: center; vertical-align: middle; padding: 6px 4px; }
    .items .num { text-align: right; direction: ltr; white-space: nowrap; font-weight: 600; }
    .items .thumb {
        width: 84px; height: 84px; object-fit: cover;
        border: 1px solid #b8cce8; border-radius: 6px;
        display: block; margin: 0 auto;
    }
    .items .thumb-empty {
        width: 84px; height: 84px; background: #f5f8fc;
        border: 1px solid #b8cce8; border-radius: 6px; margin: 0 auto;
    }
    .items .section-row td {
        background: #eef4fb !important; font-weight: 700; font-size: 12px; padding: 9px 8px;
        border: none;
        border-bottom: 1px solid #c5d8ef;
        color: #1a437f; text-align: center;
    }

    .totals { width: 45%; margin-left: auto; border-collapse: collapse; margin-bottom: 12px; }
    .totals td { padding: 4px 6px; font-size: 11px; color: #333; }
    .totals .label { text-align: right; color: #555; }
    .totals .val { text-align: right; white-space: nowrap; width: 120px; direction: ltr; color: #333; }
    .totals .grand td {
        border-top: 2px solid #2177cf; border-bottom: 2px solid #2177cf;
        font-size: 14px; font-weight: 700; padding-top: 8px; padding-bottom: 8px; color: #2177cf;
    }
    .totals tr.disc-row .label,
    .totals tr.disc-row .val { color: #c0392b; font-weight: 700; }

    .comments-h { font-weight: 700; font-size: 12px; margin: 14px 0 6px; color: #333; }
    .comments { white-space: pre-wrap; font-size: 11px; color: #444; margin-bottom: 8px; }

    .foot-company {
        margin-top: 28px;
        margin-bottom: 4px;
        padding: 0 20px;
        font-size: 8.5px;
        color: #555;
        text-align: center;
        line-height: 1.5;
        letter-spacing: 0.15px;
    }

    .sign-table { width: 100%; border-collapse: collapse; margin-top: 28px; table-layout: fixed; }
    .sign-table td {
        width: 50%; vertical-align: top; border: none; padding: 0 18px; text-align: center;
    }
    .sign-table td:first-child { padding-left: 0; }
    .sign-table td:last-child { padding-right: 0; }
    .sig-box { height: 220px; width: 100%; display: table; margin: 0 auto 8px; }
    .sig-img {
        display: table-cell; vertical-align: bottom; text-align: center;
        height: 220px; width: 100%;
    }
    .sig-img img {
        width: 200px; height: auto; max-width: 200px; max-height: 210px;
        display: inline-block; object-fit: contain;
    }
    .sig-label {
        border-top: 1.5px solid #222; padding-top: 8px; margin: 0 auto;
        font-size: 11px; color: #333; font-weight: 700; width: 85%;
    }
    .sig-name {
        font-size: 11px; color: #444; margin-top: 6px; line-height: 1.35;
        width: 85%; margin-left: auto; margin-right: auto; min-height: 28px;
    }

    .footer {
        position: absolute;
        left: 0;
        right: 0;
        bottom: 0;
        height: 72px;
        overflow: hidden;
        margin-top: auto;
    }
    .thanks-bar {
        position: absolute;
        top: 6px;
        left: 50%;
        transform: translateX(-50%);
        background: #fff;
        padding: 0 10px;
        font-size: 12px;
        font-weight: 700;
        color: #1a437f;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 8px;
        white-space: nowrap;
        font-family: 'Cairo', 'CairoFallback', sans-serif;
    }
    .thanks-bar .line { width: 42px; height: 1.2px; background: #1a437f; }
    .thanks-bar .leaf { width: 16px; height: 16px; display: block; }
    .wave-wrap { position: absolute; left: 0; right: 0; bottom: 0; height: 52px; line-height: 0; }
    .wave-wrap img { width: 100%; height: 52px; display: block; object-fit: fill; }
    .circuit {
        position: absolute; left: 6px; bottom: 3px; width: 150px; height: 40px;
        opacity: 0.75; pointer-events: none;
    }

    @media print {
        @page { size: A4 portrait; margin: 0; }
        html, body, .page-wrap { background: #fff !important; padding: 0 !important; margin: 0 !important; gap: 0 !important; }
        .page-wrap { display: block !important; }
        .sheet {
            width: 210mm !important;
            min-height: 297mm !important;
            padding: 10px 36px 24px !important;
            margin: 0 !important;
            page-break-after: always;
            break-after: page;
        }
        .sheet:last-child { page-break-after: auto; break-after: auto; }
        .sheet-summary { padding: 90px 36px 70px 36px !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
</head>
<body>
<div class="page-wrap">

@php
    $docTitleShort = ucfirst(strtolower($docTitleEn));
    $totalDiscount = round((float) ($discounts['line_discount_total'] ?? 0) + (float) ($discounts['global_discount'] ?? 0), 2);
@endphp

<div class="sheet">
@if(!empty($logoDataUri))
<div class="watermark"><img src="{{ $logoDataUri }}" alt=""></div>
@endif

<table class="header">
    <tr>
        <td class="header-brand">
            <div class="header-brand-inner">
                <div class="brand-head">
                    <div class="brand-name ar">{{ $companyNameAr }}</div>
                    <div class="brand-name-en en">{{ $companyNameEn }}</div>
                </div>
                <div class="brand-head-line"></div>
                <div class="header-contact-rows">
                    <div class="row"><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></div>
                    <div class="row"><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></div>
                    <div class="row"><img src="{{ $iconLocation }}" alt=""><span class="ar">{{ $addressAr }}</span></div>
                </div>
            </div>
        </td>
        <td class="header-center">
            @if(!empty($logoDataUri))
                <img class="logo" src="{{ $logoDataUri }}" alt="">
            @endif
        </td>
        <td class="doc-cell">
            <div class="doc-right">
                <div class="doc-head">
                    <div class="ar-title">{{ $docTitleAr }}</div>
                    <div class="en-title en">{{ $docTitleEn }}</div>
                </div>
                <div class="doc-head-line"></div>
                <div class="meta-box">
                    <div class="meta-line">
                        <span class="meta-lab">{{ $numberLabelAr }} / {{ $docTitleShort }} No</span>
                        <span class="meta-val en">{{ $docNumber }}</span>
                    </div>
                    <div class="meta-line">
                        <span class="meta-lab">التاريخ / Date</span>
                        <span class="meta-val en">{{ $dateLabel }}</span>
                    </div>
                    @if(!empty($extraMetaRows))
                        @foreach($extraMetaRows as $row)
                        <div class="meta-line">
                            <span class="meta-lab">{{ $row['label'] }}</span>
                            <span class="meta-val en">{{ $row['value'] }}</span>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>
        </td>
    </tr>
</table>

@if(count($items))
<div class="client-for-bar">
    <div class="meta-line">
        <span class="meta-lab">من / For</span>
        <span class="meta-val {{ !empty($clientNameIsArabic) ? 'ar' : 'en' }}">{{ $clientName }}</span>
    </div>
</div>
<table class="items">
    <colgroup>
        <col style="width:9%">
        <col style="width:34%">
        <col style="width:14%">
        <col style="width:9%">
        <col style="width:16%">
        <col style="width:18%">
    </colgroup>
    <thead>
        <tr>
            <th>Code</th>
            <th>Description</th>
            <th>Image</th>
            <th>Qty</th>
            <th>Rate</th>
            <th>Amount</th>
        </tr>
    </thead>
    <tbody>
        @foreach($items as $item)
        @if(!empty($item['is_section']))
        <tr class="section-row">
            <td colspan="6" class="{{ !empty($item['descriptionIsArabic']) ? 'ar' : '' }}">{{ $item['description'] }}</td>
        </tr>
        @else
        <tr>
            <td class="code">{{ $item['code'] }}</td>
            <td class="desc {{ !empty($item['descriptionIsArabic']) ? 'ar' : '' }}">{!! nl2br(e($item['description'])) !!}</td>
            <td class="img-cell">
                @if(!empty($item['imageDataUri']))
                    <img class="thumb" src="{{ $item['imageDataUri'] }}" alt="">
                @else
                    <div class="thumb-empty"></div>
                @endif
            </td>
            <td class="num">{{ rtrim(rtrim(number_format((float)$item['quantity'], 2, '.', ''), '0'), '.') }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)$item['rate'], 2) }}</td>
            <td class="num">{{ $currency }} {{ number_format((float)($item['final_amount'] ?? $item['amount']), 2) }}</td>
        </tr>
        @endif
        @endforeach
    </tbody>
</table>
@endif
</div>

<div class="sheet sheet-summary">
<div class="summary-content">
<table class="totals">
    @if($totalDiscount > 0)
    <tr>
        <td class="label">Subtotal</td>
        <td class="val">{{ $currency }} {{ number_format((float)$discounts['gross_subtotal'], 2) }}</td>
    </tr>
    <tr class="disc-row">
        <td class="label">Discount</td>
        <td class="val">− {{ $currency }} {{ number_format($totalDiscount, 2) }}</td>
    </tr>
    @endif
    <tr>
        <td class="label">Subtotal</td>
        <td class="val">{{ $currency }} {{ number_format((float)$discounts['net_before_tax'], 2) }}</td>
    </tr>
    <tr>
        <td class="label">TAX {{ rtrim(rtrim(number_format((float)$taxPercent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $currency }} {{ number_format((float)$taxAmount, 2) }}</td>
    </tr>
    <tr>
        <td class="label">Total</td>
        <td class="val">{{ $currency }} {{ number_format((float)$discounts['net_before_tax'] + (float)$taxAmount, 2) }}</td>
    </tr>
    @if((float)$withholdingPercent > 0 || (float)$withholdingAmount > 0)
    <tr>
        <td class="label">Withholding Tax {{ rtrim(rtrim(number_format((float)$withholdingPercent, 2), '0'), '.') }}%</td>
        <td class="val">{{ $currency }} {{ number_format((float)$withholdingAmount, 2) }}</td>
    </tr>
    @endif
    @if(!empty($showPaid))
    <tr>
        <td class="label">Paid</td>
        <td class="val">{{ $currency }} {{ number_format((float)$paid, 2) }}</td>
    </tr>
    @endif
    <tr class="grand">
        <td class="label">{{ $grandLabel }}</td>
        <td class="val">{{ $currency }} {{ number_format((float)$grandTotal, 2) }}</td>
    </tr>
</table>

@if(!empty($notes))
<div class="comments-h">Comments</div>
<div class="comments {{ !empty($notesIsArabic) ? 'ar' : '' }}">{!! nl2br(e($notes)) !!}</div>
@endif

<div class="foot-company en">{{ $companyLegalName }}</div>

<table class="sign-table">
    <tr>
        <td>
            <div class="sig-box">
                <div class="sig-img">
                    @if(!empty($signatureDataUri))
                        <img src="{{ $signatureDataUri }}" alt="">
                    @endif
                </div>
            </div>
            <div class="sig-label">Company signature</div>
            @if(!empty($signatureName))
                <div class="sig-name">{{ $signatureName }}</div>
            @else
                <div class="sig-name">&nbsp;</div>
            @endif
        </td>
        <td>
            <div class="sig-box">
                <div class="sig-img">&nbsp;</div>
            </div>
            <div class="sig-label">Client's signature</div>
            @if(!empty($clientName) && $clientName !== '—')
                <div class="sig-name {{ !empty($clientNameIsArabic) ? 'ar' : '' }}">{{ $clientName }}</div>
            @else
                <div class="sig-name">&nbsp;</div>
            @endif
        </td>
    </tr>
</table>
</div>

<div class="footer">
    <div class="thanks-bar">
        <span class="line"></span>
        <svg class="leaf" viewBox="0 0 24 24" fill="#1a437f">
            <path d="M12 2C8 6 6 10 6 14c0 3.3 2.7 6 6 8 3.3-2 6-4.7 6-8 0-4-2-8-6-12z"/>
            <path d="M12 8v12" stroke="#fff" stroke-width="1.6" fill="none"/>
            <path d="M12 11 L9.5 14.5 L12 13.2 L14.5 17 Z" fill="#fff"/>
        </svg>
        <span class="ar">شكراً لتعاملكم معنا</span>
        <svg class="leaf" viewBox="0 0 24 24" fill="#1a437f">
            <path d="M12 2C8 6 6 10 6 14c0 3.3 2.7 6 6 8 3.3-2 6-4.7 6-8 0-4-2-8-6-12z"/>
            <path d="M12 8v12" stroke="#fff" stroke-width="1.6" fill="none"/>
            <path d="M12 11 L9.5 14.5 L12 13.2 L14.5 17 Z" fill="#fff"/>
        </svg>
        <span class="line"></span>
    </div>
    @if(!empty($waveSvg))
    <div class="wave-wrap" aria-hidden="true">
        <img src="{{ $waveSvg }}" alt="">
        <svg class="circuit" viewBox="0 0 150 42" xmlns="http://www.w3.org/2000/svg">
            <g fill="none" stroke="#9ec4ea" stroke-width="1.15">
                <path d="M2 36 H26 V18 H48 V30 H78 V14 H108"/>
                <path d="M16 36 V24 H38"/>
                <path d="M48 18 H66 V8 H90"/>
                <path d="M78 30 H96 V36 H124"/>
                <path d="M108 14 V6 H132"/>
            </g>
            <g fill="#c5def6">
                <circle cx="2" cy="36" r="2"/><circle cx="26" cy="36" r="2"/>
                <circle cx="26" cy="18" r="2"/><circle cx="48" cy="18" r="2"/>
                <circle cx="48" cy="30" r="2"/><circle cx="78" cy="30" r="2"/>
                <circle cx="78" cy="14" r="2"/><circle cx="108" cy="14" r="2"/>
                <circle cx="16" cy="24" r="1.6"/><circle cx="66" cy="8" r="1.6"/>
                <circle cx="90" cy="8" r="1.6"/><circle cx="96" cy="36" r="1.6"/>
                <circle cx="124" cy="36" r="1.6"/><circle cx="132" cy="6" r="1.6"/>
            </g>
        </svg>
    </div>
    @endif
</div>
</div>

</div>
</body>
</html>
