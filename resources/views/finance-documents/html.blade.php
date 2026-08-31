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
        justify-content: center;
        background: #f4f7fb;
        padding: 16px;
    }
    .sheet {
        width: 794px;
        max-width: 100%;
        min-height: 1123px;
        background: #fff;
        padding: 12px 18px 24px;
        position: relative;
        overflow: visible;
    }
    body.fd-capture .page-wrap {
        min-height: auto !important;
        display: block !important;
        background: #fff !important;
        padding: 0 !important;
    }
    body.fd-capture .sheet {
        width: 794px !important;
        max-width: 794px !important;
        overflow: visible !important;
        margin: 0 auto;
        box-shadow: none !important;
    }

    .doc-first-pages { position: relative; }
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

    .top { width: 100%; border-collapse: collapse; margin-bottom: 14px; direction: ltr; table-layout: fixed; }
    .top td { vertical-align: top; border: none; padding: 0; }
    .left-brand { width: 58%; padding-right: 12px; }
    .right-doc { width: 42%; text-align: right; vertical-align: top !important; padding-top: 4px; }

    .brand-inner { width: 100%; border-collapse: collapse; }
    .brand-inner td { border: none; vertical-align: middle; padding: 0; }
    .logo-td { width: 140px; padding-right: 12px; }
    .brand img.logo { width: 140px; max-width: 140px; max-height: 110px; height: auto; display: block; object-fit: contain; }
    .brand-name { font-size: 22px; font-weight: 700; color: #1a437f; line-height: 1.2; direction: rtl; text-align: left; margin-bottom: 4px; }
    .brand-contact { font-size: 12px; color: #1a437f; font-weight: 600; }
    .brand-contact .row { display: flex; align-items: center; gap: 8px; margin: 2px 0; direction: ltr; justify-content: flex-start; white-space: nowrap; }
    .brand-contact img { width: 15px; height: 15px; }
    .trn-line { font-size: 10px; color: #2f5f9e; font-weight: 700; margin-top: 3px; direction: ltr; text-align: left; }

    .doc-title { font-size: 28px; font-weight: 700; margin: 0 0 8px; color: #222; }
    .meta { margin: 2px 0; font-size: 11px; color: #333; }

    .for { margin: 12px 0 14px; font-size: 12px; color: #333; }

    .items { width: 100%; border-collapse: collapse; margin-bottom: 12px; position: relative; z-index: 1; }
    .items th {
        background: #f3f3f3; border-top: 1px solid #ddd; border-bottom: 1px solid #ddd;
        text-align: left; padding: 7px 6px; font-size: 11px; color: #444; font-weight: 700;
    }
    .items td { border-bottom: 1px solid #eee; padding: 8px 6px; vertical-align: top; color: #333; }
    .items .num { text-align: right; direction: ltr; white-space: nowrap; }
    .items .thumb { width: 126px; height: 126px; object-fit: cover; border: 1px solid #ddd; border-radius: 6px; display: block; }
    .items .thumb-empty { width: 126px; height: 126px; background: #f5f5f5; border: 1px solid #eee; border-radius: 6px; }
    .items .section-row td {
        background: #e8eef5; font-weight: 700; font-size: 13px; padding: 10px 8px;
        border-top: 1px solid #c5d4e8; border-bottom: 1px solid #c5d4e8; color: #1e3a5f; text-align: center;
    }

    .summary-page {
        page-break-before: always;
        break-before: page;
        padding-top: 8px;
        position: relative;
        min-height: 520px;
        padding-bottom: 78px;
    }

    .totals { width: 45%; margin-left: auto; border-collapse: collapse; margin-bottom: 12px; }
    .totals td { padding: 4px 6px; font-size: 11px; }
    .totals .label { text-align: right; color: #555; }
    .totals .val { text-align: right; white-space: nowrap; width: 120px; direction: ltr; }
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
        html, body, .page-wrap { background: #fff !important; padding: 0 !important; margin: 0 !important; }
        .page-wrap { display: block !important; }
        .sheet { width: 210mm !important; padding: 10px 12px 24px !important; margin: 0 !important; }
        .summary-page { padding-bottom: 70px !important; min-height: 260mm !important; }
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
    }
</style>
</head>
<body>
<div class="page-wrap">
<div class="sheet">

@php
    $docTitleShort = ucfirst(strtolower($docTitleEn));
    $totalDiscount = round((float) ($discounts['line_discount_total'] ?? 0) + (float) ($discounts['global_discount'] ?? 0), 2);
@endphp

<div class="doc-first-pages">
@if(!empty($logoDataUri))
<div class="watermark"><img src="{{ $logoDataUri }}" alt=""></div>
@endif

<table class="top">
    <tr>
        <td class="left-brand">
            <table class="brand-inner">
                <tr>
                    <td class="logo-td">
                        @if(!empty($logoDataUri))
                            <img class="logo" src="{{ $logoDataUri }}" alt="">
                        @endif
                    </td>
                    <td>
                        <div class="brand-name ar">{{ $companyNameAr }}</div>
                        <div class="brand-contact">
                            <div class="row"><img src="{{ $iconPhone }}" alt=""><span class="en">{{ $phone }}</span></div>
                            <div class="row"><img src="{{ $iconEmail }}" alt=""><span class="en">{{ $email }}</span></div>
                            <div class="row"><img src="{{ $iconLocation }}" alt=""><span class="ar">{{ $addressAr }}</span></div>
                            @if(!empty($trn))
                                <div class="trn-line en">TRN: {{ $trn }}</div>
                            @endif
                        </div>
                    </td>
                </tr>
            </table>
        </td>
        <td class="right-doc">
            <p class="doc-title">{{ $docTitleShort }}</p>
            <p class="meta"><strong>{{ $docTitleShort }} No:</strong> <span class="en">{{ $docNumber }}</span></p>
            <p class="meta"><strong>Date:</strong> <span class="en">{{ $dateLabel }}</span></p>
            @if(!empty($extraMetaRows))
                @foreach($extraMetaRows as $row)
                <p class="meta"><strong>{{ $row['label'] }}:</strong> <span class="en">{{ $row['value'] }}</span></p>
                @endforeach
            @endif
        </td>
    </tr>
</table>

<div class="for">
    <strong>For:</strong>
    <span class="{{ !empty($clientNameIsArabic) ? 'ar' : '' }}">{{ $clientName }}</span>
</div>

@if(count($items))
<table class="items">
    <thead>
        <tr>
            <th style="width:10%">Code</th>
            <th style="width:32%">Description</th>
            <th style="width:18%">Image</th>
            <th style="width:8%" class="num">Quantity</th>
            <th style="width:12%" class="num">Rate</th>
            <th style="width:14%" class="num">Amount</th>
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
            <td>{{ $item['code'] }}</td>
            <td class="{{ !empty($item['descriptionIsArabic']) ? 'ar' : '' }}">{!! nl2br(e($item['description'])) !!}</td>
            <td>
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

<div class="summary-page">
<table class="totals">
    @if($totalDiscount > 0)
    <tr class="disc-row">
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
</div>
</body>
</html>
